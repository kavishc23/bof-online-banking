@extends('layouts.app')

@php
    $pageTitle = 'Bill Payment - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Pay a Bill</h2>
    <p>Pay utility and service providers directly from your linked bank account.</p>
@endsection

@push('styles')
<style>
    .content-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 24px;
    }

    .panel {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    body.dark-mode .panel {
        background: rgba(17,24,39,0.92);
    }

    .panel h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .panel h3 {
        color: #bfdbfe;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
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
    select,
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
    body.dark-mode select,
    body.dark-mode textarea {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    input:focus,
    select:focus,
    textarea:focus {
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

    .info-card {
        background: linear-gradient(135deg, #163d7a, #1d4ed8);
        color: white;
        border-radius: 18px;
        padding: 22px;
        margin-bottom: 18px;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
    }

    .info-card h4 {
        margin: 0 0 8px;
        font-size: 16px;
        color: #dbeafe;
        font-weight: 500;
    }

    .info-card .balance {
        font-size: 28px;
        font-weight: bold;
        margin: 12px 0;
    }

    .info-card .meta {
        font-size: 14px;
        color: #dbeafe;
        margin-top: 8px;
    }

    .payment-summary {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }

    body.dark-mode .payment-summary,
    body.dark-mode .tips-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .payment-summary h4 {
        margin-top: 0;
        margin-bottom: 12px;
        color: #163d7a;
    }

    body.dark-mode .payment-summary h4,
    body.dark-mode .tips-box h4 {
        color: #bfdbfe;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    body.dark-mode .summary-row {
        border-bottom-color: #334155;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row strong {
        color: #111827;
    }

    body.dark-mode .summary-row strong {
        color: #f3f4f6;
    }

    .tips-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
    }

    .tips-box h4 {
        margin-top: 0;
        color: #163d7a;
        margin-bottom: 12px;
    }

    .tips-box p {
        margin: 0 0 10px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.5;
    }

    body.dark-mode .tips-box p {
        color: #cbd5e1;
    }

    .tips-box p:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 960px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <div class="content-grid">
        <section class="panel">
            <h3>Bill Payment Details</h3>
            <p class="section-note">Choose the account to debit and select a supported biller below.</p>

            <form method="POST" action="{{ route('bill-payment.submit') }}">
                @csrf

                <label for="from_account_id">From Account</label>
                <select name="from_account_id" id="from_account_id" required>
                    @foreach($customer['accounts'] ?? [] as $account)
                        <option value="{{ $account['id'] }}" {{ old('from_account_id') == $account['id'] ? 'selected' : '' }}>
                            {{ $account['accountNumber'] }} - {{ $account['accountType'] }} - ${{ number_format((float)($account['balance'] ?? 0), 2) }}
                        </option>
                    @endforeach
                </select>

                <label for="biller_id">Biller Name</label>
                <select id="biller_id" name="biller_id" required>
                    <option value="">-- Select Biller --</option>
                    @foreach($billers as $biller)
                        <option
                            value="{{ $biller['id'] }}"
                            data-reference-label="{{ $biller['referenceLabel'] ?? 'Reference' }}"
                            data-reference-placeholder="{{ $biller['referencePlaceholder'] ?? 'Enter reference' }}"
                            {{ old('biller_id') == $biller['id'] ? 'selected' : '' }}
                        >
                            {{ $biller['name'] ?? '' }}
                        </option>
                    @endforeach
                </select>

                <label for="bill_reference" id="bill-reference-label">Bill Reference</label>
                <input
                    type="text"
                    id="bill_reference"
                    name="bill_reference"
                    value="{{ old('bill_reference') }}"
                    placeholder="Enter bill reference"
                >

                <label for="amount">Amount</label>
                <input
                    type="number"
                    step="0.01"
                    min="1"
                    id="amount"
                    name="amount"
                    value="{{ old('amount') }}"
                    placeholder="Enter amount"
                    required
                >

                <label for="notes">Notes</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    placeholder="Optional payment note"
                >{{ old('notes') }}</textarea>

                <button type="submit" class="submit-btn">Pay Bill</button>
            </form>
        </section>

        <section>
            @php
                $firstAccount = $customer['accounts'][0] ?? null;
            @endphp

            @if($firstAccount)
                <div class="info-card">
                    <h4>Primary Payment Account</h4>
                    <div class="balance">${{ number_format((float)($firstAccount['balance'] ?? 0), 2) }}</div>
                    <div class="meta">Account Number: {{ $firstAccount['accountNumber'] ?? '' }}</div>
                    <div class="meta">Account Type: {{ $firstAccount['accountType'] ?? '' }}</div>
                </div>
            @endif

            <div class="payment-summary">
                <h4>Supported Billers</h4>
                @forelse($billers as $biller)
                    <div class="summary-row">
                        <span>{{ $biller['name'] ?? '' }}</span>
                        <strong>{{ $biller['category'] ?? '' }}</strong>
                    </div>
                @empty
                    <div class="summary-row">
                        <span>No active billers found</span>
                        <strong>-</strong>
                    </div>
                @endforelse
            </div>

            <div class="tips-box">
                <h4>Bill Payment Tips</h4>
                <p>Check the biller name and reference number carefully before submitting payment.</p>
                <p>Ensure the selected account has enough balance to complete the payment.</p>
                <p>Completed bill payments will appear in your transaction history and dashboard activity.</p>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    const billerSelect = document.getElementById('biller_id');
    const referenceLabel = document.getElementById('bill-reference-label');
    const referenceInput = document.getElementById('bill_reference');

    function updateBillerReferenceUI() {
        if (!billerSelect) return;

        const selectedOption = billerSelect.options[billerSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            referenceLabel.textContent = 'Bill Reference';
            referenceInput.placeholder = 'Enter bill reference';
            return;
        }

        const label = selectedOption.getAttribute('data-reference-label') || 'Bill Reference';
        const placeholder = selectedOption.getAttribute('data-reference-placeholder') || 'Enter bill reference';

        referenceLabel.textContent = label;
        referenceInput.placeholder = placeholder;
    }

    if (billerSelect) {
        billerSelect.addEventListener('change', updateBillerReferenceUI);
        updateBillerReferenceUI();
    }
</script>
@endpush