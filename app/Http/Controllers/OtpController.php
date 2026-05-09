<?php

namespace App\Http\Controllers;

use App\Http\Requests\OtpVerificationRequest;
use App\Services\BofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function __construct(private readonly BofService $bofService) {}

    public function show(): View|RedirectResponse
    {
        if (! session()->has('pending_otp') || ! session()->has('pending_action') || ! session()->has('pending_payload')) {
            return redirect('/dashboard')->with('error', 'No OTP verification is pending.');
        }

        return view('otp-verification');
    }

    public function verify(OtpVerificationRequest $request): RedirectResponse
    {
        $otpExpiresAt = session('otp_expires_at');
        $pendingOtp = session('pending_otp');
        $pendingAction = session('pending_action');
        $pendingPayload = session('pending_payload');

        if (! $otpExpiresAt || now()->greaterThan($otpExpiresAt)) {
            session()->forget(['pending_otp', 'pending_action', 'pending_payload', 'otp_expires_at']);

            return redirect('/dashboard')->with('error', 'OTP has expired. Please try again.');
        }

        if (! $pendingOtp || ! $pendingAction || ! $pendingPayload) {
            return redirect('/dashboard')->with('error', 'OTP session expired.');
        }

        if ((string) $request->otp_code !== (string) $pendingOtp) {
            return back()->withInput()->with('error', 'Invalid OTP code.');
        }

        session()->forget(['pending_otp', 'pending_action', 'pending_payload', 'otp_expires_at']);

        $jwt = session('jwt');
        $customer = session('customer');
        $user = session('user');

        if ($pendingAction === 'transfer') {
            $pendingRequest = Request::create('/transfer', 'POST', $pendingPayload['form_data'] ?? []);

            return $this->bofService->processImmediateTransfer($pendingRequest, $jwt, $customer, $user);
        }

        if ($pendingAction === 'bill_payment') {
            $pendingRequest = Request::create('/bill-payment', 'POST', $pendingPayload['form_data'] ?? []);

            return $this->bofService->processImmediateBillPayment($pendingRequest, $jwt, $customer, $user);
        }

        return redirect('/dashboard')->with('error', 'Unknown OTP action.');
    }
}
