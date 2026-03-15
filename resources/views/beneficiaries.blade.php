@extends('layouts.app')

@php
    $pageTitle = 'Saved Beneficiaries - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Saved Beneficiaries</h2>
    <p>Save frequently used recipients for faster internal and local bank transfers.</p>
@endsection

@push('styles')
<style>
    .beneficiary-grid {
        display: grid;
        grid-template-columns: 1.1fr 1.2fr;
        gap: 24px;
    }

    .panel {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    body.dark-mode .panel,
    body.dark-mode .beneficiary-card,
    body.dark-mode .summary-box {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }

    .panel h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .panel h3,
    body.dark-mode .summary-box h4,
    body.dark-mode .beneficiary-title {
        color: #bfdbfe;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
    }

    body.dark-mode .section-note,
    body.dark-mode .beneficiary-meta,
    body.dark-mode .empty-state,
    body.dark-mode .summary-text {
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
    select {
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
    body.dark-mode select {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
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
    }

    .submit-btn:hover {
        background: #1e40af;
    }

    .summary-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .summary-box h4 {
        margin-top: 0;
        margin-bottom: 12px;
        color: #163d7a;
        font-size: 16px;
    }

    .summary-text {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.55;
    }

    .beneficiary-list {
        display: grid;
        gap: 14px;
    }

    .beneficiary-card {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px 18px;
    }

    .beneficiary-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
    }

    .beneficiary-title {
        font-size: 16px;
        font-weight: 800;
        color: #163d7a;
        margin-bottom: 4px;
    }

    .beneficiary-subtitle {
        font-size: 12px;
        color: #64748b;
    }

    body.dark-mode .beneficiary-subtitle {
        color: #cbd5e1;
    }

    .beneficiary-meta {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
    }

    .badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-internal {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-local {
        background: #ede9fe;
        color: #6d28d9;
    }

    .empty-state {
        color: #6b7280;
        font-size: 15px;
    }

    @media (max-width: 960px) {
        .beneficiary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    @php
        $beneficiaryCount = count($beneficiaries ?? []);
    @endphp

    <div class="beneficiary-grid">
        <section class="panel">
            <h3>Add Beneficiary</h3>
            <p class="section-note">Save a recipient once and reuse them later from the transfer page.</p>

            <form method="POST" action="{{ route('beneficiaries.store') }}">
                @csrf

                <label for="nickname">Nickname</label>
                <input
                    type="text"
                    id="nickname"
                    name="nickname"
                    value="{{ old('nickname') }}"
                    placeholder="e.g. Mum, My BSP Wallet, Office Account"
                    required
                >

                <label for="beneficiary_name">Beneficiary Name</label>
                <input
                    type="text"
                    id="beneficiary_name"
                    name="beneficiary_name"
                    value="{{ old('beneficiary_name') }}"
                    placeholder="Enter beneficiary full name"
                    required
                >

                <label for="transfer_mode">Transfer Mode</label>
                <select id="transfer_mode" name="transfer_mode" required>
                    <option value="">-- Select Transfer Mode --</option>
                    <option value="Internal" {{ old('transfer_mode') === 'Internal' ? 'selected' : '' }}>Internal</option>
                    <option value="LocalBank" {{ old('transfer_mode') === 'LocalBank' ? 'selected' : '' }}>Local Bank</option>
                </select>

                <label for="institution_name">Institution Name</label>
                <input
                    type="text"
                    id="institution_name"
                    name="institution_name"
                    value="{{ old('institution_name') }}"
                    placeholder="BoF / BSP / ANZ / Westpac / BRED / M-Paisa / MyCash"
                >

                <label for="account_number">Account Number / Wallet Number</label>
                <input
                    type="text"
                    id="account_number"
                    name="account_number"
                    value="{{ old('account_number') }}"
                    placeholder="Enter destination account or wallet number"
                    required
                >

                <button type="submit" class="submit-btn">Save Beneficiary</button>
            </form>
        </section>

        <section>
            <div class="summary-box">
                <h4>Beneficiary Summary</h4>
                <div class="summary-text">
                    You currently have <strong>{{ $beneficiaryCount }}</strong> saved beneficiary{{ $beneficiaryCount == 1 ? '' : 'ies' }}.
                    Saved beneficiaries can be selected directly from the transfer page to speed up internal and local bank transfers.
                </div>
            </div>

            <div class="panel">
                <h3>Your Beneficiaries</h3>
                <p class="section-note">These saved recipients are linked to your current banking profile.</p>

                @if(count($beneficiaries ?? []) > 0)
                    <div class="beneficiary-list">
                        @foreach($beneficiaries as $beneficiary)
                            @php
                                $mode = $beneficiary['transferMode'] ?? '';
                            @endphp

                            <div class="beneficiary-card">
                                <div class="beneficiary-top">
                                    <div>
                                        <div class="beneficiary-title">{{ $beneficiary['nickname'] ?? '-' }}</div>
                                        <div class="beneficiary-subtitle">{{ $beneficiary['beneficiaryName'] ?? '-' }}</div>
                                    </div>

                                    @if($mode === 'Internal')
                                        <span class="badge badge-internal">Internal</span>
                                    @else
                                        <span class="badge badge-local">Local Bank</span>
                                    @endif
                                </div>

                                <div class="beneficiary-meta">
                                    <strong>Institution:</strong> {{ $beneficiary['institutionName'] ?? '-' }}<br>
                                    <strong>Account Number:</strong> {{ $beneficiary['accountNumber'] ?? '-' }}<br>
                                    <strong>Transfer Mode:</strong> {{ $mode ?: '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-state">No beneficiaries saved yet.</p>
                @endif
            </div>
        </section>
    </div>
@endsection