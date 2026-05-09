<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class TransferController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function index(): View|RedirectResponse
    {
        if (! session()->has('jwt')) {
            return redirect('/login');
        }

        $user = session('user');
        $email = $user['email'] ?? '';

        return view('transfer', [
            'customer' => session('customer'),
            'otherLocalBanks' => $this->bofService->fetchActiveOtherLocalBanks(session('jwt')),
            'beneficiaries' => $this->bofService->fetchBeneficiaries(session('jwt'), $email),
        ]);
    }

    public function submit(TransferRequest $request): RedirectResponse
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
            return back()->with('error', 'Selected source account not found.');
        }

        $amount = (float) $request->amount;

        try {
            if ($request->filled('is_scheduled_transfer')) {
                return $this->scheduleTransfer($request, $jwt, $fromAccount, $amount, $user);
            }

            if ($this->bofService->requiresOtp($amount)) {
                $otpCode = $this->bofService->generateOtpCode();

                session([
                    'pending_otp' => $otpCode,
                    'pending_action' => 'transfer',
                    'otp_expires_at' => now()->addMinutes(2),
                    'pending_payload' => [
                        'form_data' => $request->except(['_token']),
                    ],
                ]);

                return redirect()->route('otp.verification')
                    ->with('info', 'OTP sent to your registered mobile number. (Demo OTP: '.$otpCode.')');
            }

            return $this->bofService->processImmediateTransfer($request, $jwt, $customer, $user);
        } catch (Throwable $exception) {
            return $this->bofService->handleException($exception, 'Transfer could not be processed. Please try again.');
        }
    }

    private function scheduleTransfer(TransferRequest $request, string $jwt, array $fromAccount, float $amount, array $user): RedirectResponse
    {
        $referenceNumber = 'SCH-TXN-'.time();

        if ($request->transfer_mode === 'internal') {
            $accountsResponse = Http::withToken($jwt)->get('http://localhost:1337/api/accounts?populate=*');
            $toAccount = collect($accountsResponse->json()['data'] ?? [])
                ->first(fn ($account) => isset($account['accountNumber']) && trim((string) $account['accountNumber']) === trim((string) $request->to_account_number));

            if (! $toAccount) {
                return back()->withInput()->with('error', 'Destination BoF account not found.');
            }

            if ((int) $toAccount['id'] === (int) $fromAccount['id']) {
                return back()->withInput()->with('error', 'Cannot transfer to the same account.');
            }

            $scheduledResponse = Http::withToken($jwt)->post('http://localhost:1337/api/scheduled-transfers', [
                'data' => [
                    'referenceNumber' => $referenceNumber,
                    'transferMode' => 'Internal',
                    'amount' => $amount,
                    'scheduledDate' => $request->scheduled_date,
                    'frequency' => $request->frequency,
                    'description' => $request->description,
                    'destinationInstitution' => 'BoF',
                    'destinationAccountNumber' => $toAccount['accountNumber'] ?? '',
                    'beneficiaryName' => $toAccount['accountHolderName'] ?? '',
                    'scheduleStatus' => 'Pending',
                    'customerEmail' => $user['email'] ?? '',
                    'sourceAccount' => $fromAccount['id'],
                    'destinationAccount' => $toAccount['id'],
                ],
            ]);

            if (! $scheduledResponse->successful()) {
                $this->bofService->reportApiFailure('scheduled_internal_transfer_create', $scheduledResponse);

                return back()->withInput()->with('error', 'Internal transfer could not be scheduled. Please try again.');
            }

            return redirect('/dashboard')->with('success', 'Internal transfer scheduled successfully.');
        }

        $selectedInstitution = $this->bofService->findSelectedInstitution($jwt, (int) $request->destination_institution_id);

        if (! $selectedInstitution) {
            return back()->withInput()->with('error', 'Selected destination institution not found.');
        }

        $scheduledResponse = Http::withToken($jwt)->post('http://localhost:1337/api/scheduled-transfers', [
            'data' => [
                'referenceNumber' => $referenceNumber,
                'transferMode' => 'LocalBank',
                'amount' => $amount,
                'scheduledDate' => $request->scheduled_date,
                'frequency' => $request->frequency,
                'description' => $request->description,
                'destinationInstitution' => $selectedInstitution['name'] ?? '',
                'destinationAccountNumber' => $request->destination_account_number,
                'beneficiaryName' => $request->beneficiary_name,
                'scheduleStatus' => 'Pending',
                'customerEmail' => $user['email'] ?? '',
                'sourceAccount' => $fromAccount['id'],
            ],
        ]);

        if (! $scheduledResponse->successful()) {
            $this->bofService->reportApiFailure('scheduled_local_bank_transfer_create', $scheduledResponse);

            return back()->withInput()->with('error', 'Local bank transfer could not be scheduled. Please try again.');
        }

        return redirect('/dashboard')->with('success', 'Local bank transfer scheduled successfully.');
    }
}
