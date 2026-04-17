@extends('layouts.app')

@php
    $pageTitle = 'Customer Profile - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Customer Profile</h2>
    <p>Review your verified banking profile, update contact details, and maintain your tax identification records.</p>
@endsection

@push('styles')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 24px;
    }

    .profile-card {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    body.dark-mode .profile-card {
        background: rgba(17,24,39,0.92);
    }

    .profile-card h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .profile-card h3,
    body.dark-mode .info-box h4,
    body.dark-mode .account-box h4 {
        color: #bfdbfe;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
        line-height: 1.6;
    }

    body.dark-mode .section-note,
    body.dark-mode .info-box p,
    body.dark-mode .info-box li,
    body.dark-mode .account-meta {
        color: #cbd5e1;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #1f2937;
    }

    body.dark-mode label {
        color: #e5e7eb;
    }

    input,
    textarea {
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 18px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        background: #fff;
        color: #111827;
    }

    body.dark-mode input,
    body.dark-mode textarea {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    input[readonly],
    textarea[readonly] {
        background: #f8fafc;
        color: #6b7280;
        cursor: not-allowed;
    }

    body.dark-mode input[readonly],
    body.dark-mode textarea[readonly] {
        background: #0f172a;
        color: #94a3b8;
    }

    .submit-btn {
        width: 100%;
        background: #1d4ed8;
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        margin-top: 8px;
    }

    .submit-btn:hover {
        background: #1e40af;
    }

    .info-box,
    .account-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }

    body.dark-mode .info-box,
    body.dark-mode .account-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .info-box h4,
    .account-box h4 {
        margin-top: 0;
        color: #163d7a;
        margin-bottom: 12px;
        font-size: 16px;
    }

    .detail-row {
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    body.dark-mode .detail-row {
        border-bottom-color: #334155;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        display: block;
        color: #6b7280;
        font-size: 12px;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }

    .detail-value {
        color: #111827;
        font-weight: 600;
        line-height: 1.6;
    }

    body.dark-mode .detail-value {
        color: #f3f4f6;
    }

    .file-link {
        display: inline-block;
        margin-top: 8px;
        color: #1d4ed8;
        font-weight: 700;
        text-decoration: none;
    }

    .file-link:hover {
        text-decoration: underline;
    }

    .locked-note {
        margin-top: -6px;
        margin-bottom: 18px;
        color: #92400e;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        line-height: 1.5;
    }

    body.dark-mode .locked-note {
        background: rgba(154, 52, 18, 0.18);
        border-color: rgba(251, 146, 60, 0.35);
        color: #fdba74;
    }

    .account-list {
        display: grid;
        gap: 12px;
    }

    .account-item {
        padding: 14px;
        border-radius: 12px;
        background: white;
        border: 1px solid #dbeafe;
    }

    body.dark-mode .account-item {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }

    .account-title {
        font-weight: 800;
        color: #163d7a;
        margin-bottom: 6px;
    }

    body.dark-mode .account-title {
        color: #bfdbfe;
    }

    .account-meta {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
    }

    .tax-chip {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #dbeafe;
        color: #1d4ed8;
    }

    body.dark-mode .tax-chip {
        background: rgba(30, 58, 138, 0.28);
        color: #bfdbfe;
    }

    @media (max-width: 960px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $accounts = $customer['accounts'] ?? [];
    $customerSince = !empty($customer['createdAt']) ? \Carbon\Carbon::parse($customer['createdAt'])->format('d M Y') : '-';
    $dob = !empty($customer['dob']) ? \Carbon\Carbon::parse($customer['dob'])->format('d M Y') : '-';
@endphp

<div class="profile-grid">
    <section class="profile-card">
        <h3>Editable Details</h3>
        <p class="section-note">
            For security and compliance reasons, only contact details and tax identification information can be updated online.
            Name, date of birth, address, and residency details are maintained as verified KYC records.
        </p>

        <form method="POST" action="{{ route('customer-profile.update') }}" enctype="multipart/form-data">
            @csrf

            <label>First Name</label>
            <input type="text" value="{{ $customer['firstName'] ?? '' }}" readonly>

            <label>Last Name</label>
            <input type="text" value="{{ $customer['lastName'] ?? '' }}" readonly>

            <label>Date of Birth</label>
            <input type="text" value="{{ $dob }}" readonly>

            <label>Residential Address</label>
            <textarea rows="3" readonly>{{ $customer['address'] ?? '' }}</textarea>

            <label>Residency Status</label>
            <input type="text" value="{{ $customer['residencyStatus'] ?? '-' }}" readonly>

            <div class="locked-note">
                Locked fields are part of your verified customer record. To change these details, please contact the bank or visit a branch with supporting documents.
            </div>

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $customer['email'] ?? '') }}"
                required
            >

            <label for="phone">Phone Number</label>
            <input
                type="text"
                id="phone"
                name="phone"
                value="{{ old('phone', $customer['phone'] ?? '') }}"
                required
            >

            <label for="tin">TIN</label>
            <input
                type="text"
                id="tin"
                name="tin"
                value="{{ old('tin', $customer['tin'] ?? '') }}"
                placeholder="Enter Tax Identification Number"
                required
            >

            <label for="tin_supporting_document">TIN Supporting Document</label>
            <input
                type="file"
                id="tin_supporting_document"
                name="tin_supporting_document"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
            >

            <p class="section-note" style="margin-top:-8px;">
                Upload a valid tax document when adding or updating your TIN. Accepted formats: PDF, JPG, PNG, DOC, DOCX.
            </p>

            <button type="submit" class="submit-btn">Update Contact & Tax Details</button>
        </form>
    </section>

    <section>
        <div class="info-box">
            <h4>Verified Customer Information</h4>

            <div class="detail-row">
                <span class="detail-label">Full Name</span>
                <span class="detail-value">{{ ($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '') }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Date of Birth</span>
                <span class="detail-value">{{ $dob }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $customer['email'] ?? '-' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Phone</span>
                <span class="detail-value">{{ $customer['phone'] ?? '-' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Address</span>
                <span class="detail-value">{{ $customer['address'] ?? '-' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">TIN</span>
                <span class="detail-value">{{ $customer['tin'] ?? 'Not provided' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Residency Status</span>
                <span class="detail-value">{{ $customer['residencyStatus'] ?? '-' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Customer Since</span>
                <span class="detail-value">{{ $customerSince }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Linked Accounts</span>
                <span class="detail-value">{{ count($accounts) }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">TIN Supporting Document</span>
                <span class="detail-value">
                    @if(!empty($customer['tinSupportingDocument']['url']))
                        <a
                            href="http://localhost:1337{{ $customer['tinSupportingDocument']['url'] }}"
                            target="_blank"
                            class="file-link"
                        >
                            View Uploaded Document
                        </a>
                    @else
                        No document uploaded
                    @endif
                </span>
            </div>
        </div>

        <div class="info-box">
            <h4>Tax Profile Summary</h4>

            <div class="detail-row">
                <span class="detail-label">Current Tax Identification Status</span>
                <span class="detail-value">
                    @if(!empty($customer['tin']))
                        <span class="tax-chip">TIN Available</span>
                    @else
                        <span class="tax-chip">TIN Missing</span>
                    @endif
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Residency Classification</span>
                <span class="detail-value">{{ $customer['residencyStatus'] ?? '-' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Tax Note</span>
                <span class="detail-value">
                    Customers without a valid TIN or with non-resident status may be subject to withholding tax treatment on interest earned.
                </span>
            </div>
        </div>

        <div class="account-box">
            <h4>Linked Accounts</h4>

            @if(count($accounts))
                <div class="account-list">
                    @foreach($accounts as $account)
                        <div class="account-item">
                            <div class="account-title">
                                {{ $account['accountType'] ?? 'Account' }} - {{ $account['accountNumber'] ?? '-' }}
                            </div>
                            <div class="account-meta">
                                Balance: {{ number_format((float)($account['balance'] ?? 0), 2) }} FJD<br>
                                Interest Rate: {{ $account['interestRate'] ?? 0 }}%<br>
                                Monthly Fee: {{ number_format((float)($account['monthlyMaintenanceFee'] ?? 0), 2) }} FJD<br>
                                Opened:
                                {{ !empty($account['openedAt']) ? \Carbon\Carbon::parse($account['openedAt'])->format('d M Y') : 'Not recorded' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="section-note" style="margin:0;">No linked accounts found.</p>
            @endif
        </div>

        <div class="info-box">
            <h4>Profile Guidance</h4>
            <p>
                Keep your email and phone number current so the bank can send alerts, OTP verification codes, and account notifications.
            </p>
            <p>
                Your TIN should match the official document you upload. This helps keep tax reporting and withholding calculations accurate.
            </p>
            <p>
                Changes to legal identity details and residency records must be completed through formal bank verification procedures.
            </p>
        </div>
    </section>
</div>
@endsection