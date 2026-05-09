@extends('admin.layout')

@section('heading', 'Account Details')
@section('subheading', $account['accountNumber'] ?? 'Account')
@section('actions')
    <a href="{{ route('admin.accounts.edit', $account['documentId'] ?? $account['id']) }}" class="admin-btn">Edit Account</a>
@endsection

@section('admin-content')
    <section class="admin-grid">
        <div class="admin-card admin-stat">
            <span>Account Type</span>
            <strong>{{ $account['accountType'] ?? '-' }}</strong>
        </div>
        <div class="admin-card admin-stat">
            <span>Balance</span>
            <strong>{{ number_format((float) ($account['balance'] ?? 0), 2) }}</strong>
        </div>
        <div class="admin-card admin-stat">
            <span>Calculated Monthly Fee</span>
            <strong>{{ number_format((float) ($account['feeSummary']['calculated_monthly_fee'] ?? 0), 2) }}</strong>
        </div>
        <div class="admin-card admin-stat">
            <span>Interest Rate</span>
            <strong>{{ $account['interestRate'] ?? '0' }}</strong>
        </div>
    </section>

    <section class="admin-card">
        <h3>Account Information</h3>
        <div class="admin-grid">
            <div><strong>Account Number</strong><br>{{ $account['accountNumber'] ?? '-' }}</div>
            <div><strong>Opened At</strong><br>{{ !empty($account['openedAt']) ? \Carbon\Carbon::parse($account['openedAt'])->format('d M Y') : '-' }}</div>
            <div><strong>Customer</strong><br>{{ $account['customer']['email'] ?? $account['customer']['firstName'] ?? $account['customer']['id'] ?? '-' }}</div>
            <div><strong>Fee Explanation</strong><br>{{ $account['feeSummary']['explanation'] ?? '-' }}</div>
        </div>
    </section>

    <section class="admin-card">
        <h3>Recent Account Transactions</h3>
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') : '-' }}</td>
                            <td>{{ $transaction['referenceNumber'] ?? '-' }}</td>
                            <td>{{ $transaction['transactionType'] ?? '-' }}</td>
                            <td>{{ number_format((float) ($transaction['amount'] ?? 0), 2) }}</td>
                            <td>{{ $transaction['transactionStatus'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No transactions found for this account.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

