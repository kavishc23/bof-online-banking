@extends('layouts.app')

@php
    $pageTitle = 'OTP Verification - BoF Online Banking';

    $customerPhone = session('customer.phone', '********');
    $maskedPhone = $customerPhone
        ? str_repeat('*', max(strlen($customerPhone) - 3, 0)) . substr($customerPhone, -3)
        : '********';
@endphp

@section('topcard')
    <h2>Secure OTP Verification</h2>
    <p>Please verify this high-value transaction using the one-time password sent to your registered mobile number.</p>
@endsection

@push('styles')
<style>
    .otp-wrapper {
        max-width: 620px;
        margin: 0 auto;
    }

    .otp-card {
        background: white;
        border-radius: 22px;
        padding: 30px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }

    body.dark-mode .otp-card {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }

    .otp-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
    }

    .otp-icon {
        width: 54px;
        height: 54px;
        min-width: 54px;
        border-radius: 16px;
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.24);
    }

    .otp-header h3 {
        margin: 0 0 6px;
        color: #163d7a;
        font-size: 1.35rem;
    }

    body.dark-mode .otp-header h3 {
        color: #bfdbfe;
    }

    .otp-header p {
        margin: 0;
        color: #6b7280;
        line-height: 1.6;
        font-size: 14px;
    }

    body.dark-mode .otp-header p {
        color: #cbd5e1;
    }

    .otp-info-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px 18px;
        margin-bottom: 22px;
    }

    body.dark-mode .otp-info-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .otp-info-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    body.dark-mode .otp-info-row {
        border-bottom-color: #334155;
    }

    .otp-info-row:last-child {
        border-bottom: none;
    }

    .otp-info-label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .otp-info-value {
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        text-align: right;
    }

    body.dark-mode .otp-info-value {
        color: #f3f4f6;
    }

    .otp-card label {
        display: block;
        margin-bottom: 10px;
        font-weight: 800;
        color: #1f2937;
    }

    body.dark-mode .otp-card label {
        color: #f3f4f6;
    }

    .otp-input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        margin-bottom: 14px;
        font-size: 20px;
        letter-spacing: 0.35em;
        text-align: center;
        font-weight: 700;
        background: #fff;
        color: #111827;
        outline: none;
        transition: 0.2s ease;
    }

    .otp-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    body.dark-mode .otp-input {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    .otp-note {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 18px;
        line-height: 1.6;
    }

    body.dark-mode .otp-note {
        color: #cbd5e1;
    }

    .otp-btn {
        width: 100%;
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 14px;
        font-weight: 800;
        cursor: pointer;
        font-size: 15px;
        box-shadow: 0 10px 22px rgba(29, 78, 216, 0.20);
        transition: 0.2s ease;
    }

    .otp-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
    }

    .otp-footer {
        margin-top: 18px;
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
    }

    body.dark-mode .otp-footer {
        color: #cbd5e1;
    }

    .otp-demo-badge {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #fff7ed;
        color: #9a3412;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid #fed7aa;
    }

    body.dark-mode .otp-demo-badge {
        background: rgba(154, 52, 18, 0.15);
        color: #fdba74;
        border-color: rgba(251, 146, 60, 0.28);
    }
</style>
@endpush

@section('content')
    <div class="otp-wrapper">
        <div class="otp-card">
            <div class="otp-header">
                <div class="otp-icon">🔐</div>
                <div>
                    <h3>Transaction Authentication Required</h3>
                    <p>
                        For your security, high-value transactions require one-time password verification before processing can continue.
                    </p>
                </div>
            </div>

            <div class="otp-info-box">
                <div class="otp-info-row">
                    <span class="otp-info-label">Delivery Method</span>
                    <span class="otp-info-value">SMS Notification</span>
                </div>
                <div class="otp-info-row">
                    <span class="otp-info-label">Registered Mobile</span>
                    <span class="otp-info-value">{{ $maskedPhone }}</span>
                </div>
                <div class="otp-info-row">
                    <span class="otp-info-label">OTP Validity</span>
                    <span class="otp-info-value">2 Minutes</span>
                </div>
            </div>

            <form method="POST" action="{{ route('otp.verification.submit') }}">
                @csrf

                <label for="otp_code">Enter OTP Code</label>
                <input
                    type="text"
                    id="otp_code"
                    name="otp_code"
                    class="otp-input"
                    placeholder="••••••"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                >

                <div class="otp-note">
                    Enter the 6-digit code sent to your mobile number to authorize this transaction.
                </div>

                <button type="submit" class="otp-btn">Verify and Continue</button>
            </form>

            <div class="otp-footer">
                Did not receive the code? In this demo, the OTP is shown in the notification message after submitting the previous form.
                <div class="otp-demo-badge">Demo Banking OTP Screen</div>
            </div>
        </div>
    </div>
@endsection