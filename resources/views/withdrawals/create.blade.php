@extends('layouts.app')

@php
    $pageTitle = 'Withdraw Funds - BoF Online Banking';
    $accounts = $customer['accounts'] ?? [];
@endphp

@section('topcard')
    <h2>Withdraw Funds</h2>
    <p>Create a cash withdrawal from one of your Bank of Fiji accounts.</p>
@endsection

@push('styles')
<style>
    .withdraw-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 24px;
    }

    .withdraw-panel,
    .withdraw-summary,
    .withdraw-tips {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    body.dark-mode .withdraw-panel,
    body.dark-mode .withdraw-summary,
    body.dark-mode .withdraw-tips {
        background: rgba(17,24,39,0.92);
    }

    .withdraw-panel h3,
    .withdraw-summary h3,
    .withdraw-tips h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .withdraw-panel h3,
    body.dark-mode .withdraw-summary h3,
    body.dark-mode .withdraw-tips h3 {
        color: #bfdbfe;
    }

    .withdraw-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
    }

    body.dark-mode .withdraw-note {
        color: #cbd5e1;
    }

    .withdraw-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #1f2937;
    }

    body.dark-mode .withdraw-field label {
        color: #e5e7eb;
    }

    .withdraw-field input,
    .withdraw-field select,
    .withdraw-field textarea {
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 18px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        background: #fff;
        color: #111827;
    }

    body.dark-mode .withdraw-field input,
    body.dark-mode .withdraw-field select,
    body.dark-mode .withdraw-field textarea {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    .withdraw-field input:focus,
    .withdraw-field select:focus,
    .withdraw-field textarea:focus {
        outline: none;
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
    }

    .withdraw-submit {
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

    .withdraw-submit:hover {
        background: #1e40af;
    }

    .balance-card {
        background: linear-gradient(135deg, #163d7a, #1d4ed8);
        color: white;
        border-radius: 18px;
        padding: 22px;
        margin-bottom: 18px;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
    }

    .balance-card h4 {
        margin: 0 0 8px;
        font-size: 16px;
        color: #dbeafe;
        font-weight: 500;
    }

    .balance-card .balance {
        font-size: 28px;
        font-weight: bold;
        margin: 12px 0;
    }

    .balance-card .meta,
    .withdraw-tips p {
        color: #dbeafe;
        font-size: 14px;
        line-height: 1.5;
    }

    .withdraw-tips {
        background: #f8fbff;
        border: 1px solid #dbeafe;
    }

    body.dark-mode .withdraw-tips {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .withdraw-tips p {
        color: #4b5563;
        margin: 0 0 10px;
    }

    body.dark-mode .withdraw-tips p {
        color: #cbd5e1;
    }

    .validation-list {
        background: #fee2e2;
        color: #991b1b;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 18px;
    }

    @media (max-width: 960px) {
        .withdraw-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <div class="withdraw-grid">
        <section class="withdraw-panel">
            <h3>Withdrawal Details</h3>
            <p class="withdraw-note">Choose your account and enter the cash amount to withdraw.</p>

            @if($errors->any())
                <div class="validation-list">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('withdraw.submit') }}">
                @csrf

                <div class="withdraw-field">
                    <label for="account_id">From Account</label>
                    <select name="account_id" id="account_id" required>
                        <option value="">-- Select Account --</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account['id'] }}" @selected((string) old('account_id') === (string) $account['id'])>
                                {{ $account['accountNumber'] ?? 'Account' }} - {{ $account['accountType'] ?? 'Account' }} - ${{ number_format((float) ($account['balance'] ?? 0), 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="withdraw-field">
                    <label for="amount">Amount</label>
                    <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" placeholder="Enter withdrawal amount" required>
                </div>

                <div class="withdraw-field">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="4" placeholder="Optional withdrawal note">{{ old('remarks') }}</textarea>
                </div>

                <button type="submit" class="withdraw-submit">Withdraw</button>
            </form>
        </section>

        <section>
            @forelse($accounts as $account)
                <div class="balance-card">
                    <h4>{{ $account['accountType'] ?? 'Account' }} Balance</h4>
                    <div class="balance">${{ number_format((float) ($account['balance'] ?? 0), 2) }}</div>
                    <div class="meta">Account Number: {{ $account['accountNumber'] ?? '-' }}</div>
                </div>
            @empty
                <div class="withdraw-summary">
                    <h3>No Accounts Found</h3>
                    <p class="withdraw-note">No customer accounts are available for withdrawal.</p>
                </div>
            @endforelse

            <div class="withdraw-tips">
                <h3>Withdrawal Notes</h3>
                <p>Withdrawals are rejected when the selected account balance is not enough.</p>
                <p>Savings account withdrawal fees are calculated by the existing monthly fee service from completed Withdrawal transactions.</p>
                <p>The first monthly Savings withdrawal is free, then each additional monthly withdrawal is charged $5.00.</p>
            </div>
        </section>
    </div>
@endsection
