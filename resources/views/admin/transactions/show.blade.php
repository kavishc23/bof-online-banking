@extends('admin.layout')

@section('heading', 'Transaction Details')
@section('subheading', $transaction['referenceNumber'] ?? 'Transaction')
@section('actions')
    <a href="{{ route('admin.transactions.index') }}" class="admin-btn secondary">Back to Transactions</a>
@endsection

@section('admin-content')
    <section class="admin-grid">
        <div class="admin-card admin-stat">
            <span>Type</span>
            <strong>{{ $transaction['transactionType'] ?? '-' }}</strong>
        </div>
        <div class="admin-card admin-stat">
            <span>Amount</span>
            <strong>{{ number_format((float) ($transaction['amount'] ?? 0), 2) }}</strong>
        </div>
        <div class="admin-card admin-stat">
            <span>Status</span>
            <strong>{{ $transaction['transactionStatus'] ?? '-' }}</strong>
        </div>
        <div class="admin-card admin-stat">
            <span>Date</span>
            <strong>{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') : '-' }}</strong>
        </div>
    </section>

    <section class="admin-card">
        <h3>Transaction Information</h3>
        <div class="admin-detail-grid">
            <div><strong>Reference Number</strong><br>{{ $transaction['referenceNumber'] ?? '-' }}</div>
            <div><strong>Account</strong><br>{{ $transaction['account']['accountNumber'] ?? $transaction['account']['id'] ?? '-' }}</div>
            <div><strong>Source Account</strong><br>{{ $transaction['sourceAccount']['accountNumber'] ?? $transaction['sourceAccount']['id'] ?? '-' }}</div>
            <div><strong>Destination Account</strong><br>{{ $transaction['destinationAccount']['accountNumber'] ?? $transaction['destinationAccount']['id'] ?? '-' }}</div>
            <div><strong>Destination Institution</strong><br>{{ $transaction['destinationInstitution'] ?? '-' }}</div>
            <div><strong>Destination Account Number</strong><br>{{ $transaction['destinationAccountNumber'] ?? '-' }}</div>
            <div><strong>Beneficiary Name</strong><br>{{ $transaction['beneficiaryName'] ?? '-' }}</div>
            <div><strong>Remarks</strong><br>{{ $transaction['remarks'] ?? '-' }}</div>
            <div class="admin-detail-wide"><strong>Description</strong><br>{{ $transaction['description'] ?? '-' }}</div>
        </div>
    </section>
@endsection
