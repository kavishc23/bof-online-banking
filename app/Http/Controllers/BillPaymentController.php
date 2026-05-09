<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillPaymentRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class BillPaymentController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function index(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        return view('bill-payment', [
            'customer' => session('customer'),
            'billers' => $this->bofService->fetchActiveBillers(session('jwt')),
        ]);
    }

    public function submit(BillPaymentRequest $request): RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $jwt = session('jwt');
        $customer = session('customer');
        $user = session('user');

        if (! $customer || empty($customer['accounts'])) {
            return back()->with('error', 'No customer accounts found.');
        }

        $fromAccount = $this->bofService->findCustomerAccount($customer, (int) $request->from_account_id);

        if (! $fromAccount) {
            return back()->with('error', 'Selected account not found.');
        }

        $selectedBiller = $this->bofService->findSelectedBiller($jwt, (int) $request->biller_id);

        if (! $selectedBiller) {
            return back()->withInput()->with('error', 'Selected biller not found.');
        }

        $amount = (float) $request->amount;

        try {
            if ($request->filled('is_scheduled_bill')) {
                return $this->scheduleBillPayment($request, $jwt, $fromAccount, $selectedBiller, $amount, $user);
            }

            if ($this->bofService->requiresOtp($amount)) {
                $otpCode = $this->bofService->generateOtpCode();

                session([
                    'pending_otp' => $otpCode,
                    'pending_action' => 'bill_payment',
                    'otp_expires_at' => now()->addMinutes(2),
                    'pending_payload' => [
                        'form_data' => $request->except(['_token']),
                    ],
                ]);

                return redirect()->route('otp.verification')
                    ->with('info', 'OTP sent to your registered mobile number. (Demo OTP: '.$otpCode.')');
            }

            return $this->bofService->processImmediateBillPayment($request, $jwt, $customer, $user);
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Bill payment could not be processed. Please try again.');
        }
    }

    private function scheduleBillPayment(BillPaymentRequest $request, string $jwt, array $fromAccount, array $selectedBiller, float $amount, array $user): RedirectResponse
    {
        $scheduledBillResponse = Http::withToken($jwt)->post('http://localhost:1337/api/scheduled-bill-payments', [
            'data' => [
                'referenceNumber' => 'SCH-BILL-'.time(),
                'amount' => $amount,
                'scheduledDate' => $request->scheduled_date,
                'frequency' => $request->frequency,
                'billReference' => $request->bill_reference,
                'notes' => $request->notes,
                'scheduleStatus' => 'Pending',
                'customerEmail' => $user['email'] ?? '',
                'billerName' => $selectedBiller['name'] ?? '',
                'sourceAccount' => $fromAccount['id'],
            ],
        ]);

        if (! $scheduledBillResponse->successful()) {
            $this->bofService->reportApiFailure('scheduled_bill_payment_create', $scheduledBillResponse);

            return back()->withInput()->with('error', 'Bill payment could not be scheduled. Please try again.');
        }

        return redirect('/dashboard')->with('success', 'Bill payment scheduled successfully.');
    }
}
