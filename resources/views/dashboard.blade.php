@extends('layouts.app')

@php
    $pageTitle = 'Dashboard - BoF Online Banking';
@endphp

@section('topcard')
    <div class="db-hero">
        <div class="db-hero-copy">
            <div class="db-hero-eyebrow">Retail Banking Dashboard</div>
            <h2>Welcome back, {{ session('customer.firstName', session('user.username', 'Customer')) }}</h2>
            <p>View your balances, track recent activity, and access your banking tools in one place.</p>

            <div class="db-hero-actions">
                <a href="{{ route('transfer') }}" class="db-btn db-btn-primary">Transfer Money</a>
                <a href="{{ route('bill-payment') }}" class="db-btn db-btn-secondary">Pay Bills</a>
            </div>
        </div>

        <div class="db-hero-panel">
            <div class="db-hero-stat">
                <span>Customer Status</span>
                <strong>Active</strong>
            </div>
            <div class="db-hero-stat">
                <span>Workspace</span>
                <strong>Secure Session</strong>
            </div>
            <div class="db-hero-stat">
                <span>Last Sign In</span>
                <strong>{{ now()->format('d M Y') }}</strong>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .db-hero {
        display: grid;
        grid-template-columns: 1.5fr 0.9fr;
        gap: 22px;
        align-items: center;
    }

    .db-hero-eyebrow {
        display: inline-block;
        margin-bottom: 10px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.10);
        color: var(--primary-mid);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .db-hero h2 {
        margin: 0 0 12px;
        font-size: 2.25rem;
        line-height: 1.1;
        font-weight: 800;
        color: var(--primary-mid);
    }

    .db-hero p {
        margin: 0;
        color: var(--text-soft);
        font-size: 1rem;
        line-height: 1.7;
        max-width: 700px;
    }

    .db-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 22px;
    }

    .db-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
        transition: 0.2s ease;
    }

    .db-btn-primary {
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: #fff;
        box-shadow: 0 10px 22px rgba(29, 78, 216, 0.22);
    }

    .db-btn-primary:hover {
        transform: translateY(-1px);
    }

    .db-btn-secondary {
        background: rgba(37, 99, 235, 0.08);
        color: var(--primary-mid);
        border: 1px solid rgba(37, 99, 235, 0.12);
    }

    .db-btn-secondary:hover {
        background: rgba(37, 99, 235, 0.14);
    }

    .db-hero-panel {
        display: grid;
        gap: 12px;
    }

    .db-hero-stat {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255,255,255,0.72);
        border: 1px solid rgba(219, 234, 254, 0.95);
        box-shadow: 0 10px 24px rgba(15, 43, 91, 0.05);
    }

    body.dark-mode .db-hero-stat {
        background: rgba(17,24,39,0.84);
        border-color: rgba(51,65,85,0.95);
    }

    .db-hero-stat span {
        display: block;
        color: var(--text-soft);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .db-hero-stat strong {
        color: var(--text-main);
        font-size: 1rem;
        font-weight: 800;
    }

    .db-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 24px;
        margin-top: 24px;
    }

    .db-stack {
        display: grid;
        gap: 24px;
    }

    .db-card {
        background: var(--card-bg);
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 22px;
        padding: 22px;
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(8px);
    }

    body.dark-mode .db-card {
        background: rgba(17,24,39,0.92);
        border-color: rgba(51,65,85,0.95);
    }

    .db-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .db-card-head h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--primary-mid);
    }

    .db-card-head a {
        text-decoration: none;
        color: var(--primary-light);
        font-weight: 700;
        font-size: 14px;
    }

    .db-account-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    }

    .db-account-card {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 20px;
        background: linear-gradient(135deg, #0b2147, #184691);
        color: #fff;
        min-height: 170px;
        box-shadow: 0 14px 30px rgba(11, 33, 71, 0.22);
    }

    .db-account-card::after {
        content: "";
        position: absolute;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        top: -50px;
        right: -40px;
        background: rgba(255,255,255,0.08);
    }

    .db-account-type {
        position: relative;
        z-index: 1;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.85;
        margin-bottom: 18px;
        font-weight: 700;
    }

    .db-account-balance {
        position: relative;
        z-index: 1;
        font-size: 1.9rem;
        font-weight: 800;
        margin-bottom: 14px;
        letter-spacing: -0.02em;
    }

    .db-account-meta {
        position: relative;
        z-index: 1;
        font-size: 0.95rem;
        opacity: 0.92;
        line-height: 1.6;
    }

    .db-feature-list {
        display: grid;
        gap: 14px;
    }

    .db-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px;
        border-radius: 18px;
        background: rgba(37, 99, 235, 0.05);
        border: 1px solid rgba(37, 99, 235, 0.08);
    }

    body.dark-mode .db-feature-item {
        background: rgba(37, 99, 235, 0.08);
        border-color: rgba(96,165,250,0.12);
    }

    .db-feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
    }

    .db-feature-copy h4 {
        margin: 0 0 6px;
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .db-feature-copy p {
        margin: 0;
        color: var(--text-soft);
        font-size: 0.94rem;
        line-height: 1.6;
    }

    .db-table-wrap {
        overflow-x: auto;
    }

    .db-table {
        width: 100%;
        border-collapse: collapse;
    }

    .db-table th,
    .db-table td {
        padding: 14px 12px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        text-align: left;
        font-size: 14px;
    }

    body.dark-mode .db-table th,
    body.dark-mode .db-table td {
        border-bottom-color: rgba(51,65,85,0.95);
    }

    .db-table th {
        color: var(--text-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .db-table td {
        color: var(--text-main);
    }

    .db-amount {
        font-weight: 800;
    }

    .db-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .db-badge-completed {
        background: #dcfce7;
        color: #166534;
    }

    .db-badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .db-compact-grid {
        display: grid;
        gap: 14px;
    }

    .db-mini-card {
        padding: 18px;
        border-radius: 18px;
        background: rgba(255,255,255,0.72);
        border: 1px solid rgba(219, 234, 254, 0.95);
        box-shadow: 0 8px 20px rgba(15, 43, 91, 0.05);
    }

    body.dark-mode .db-mini-card {
        background: rgba(17,24,39,0.84);
        border-color: rgba(51,65,85,0.95);
    }

    .db-mini-card span {
        display: block;
        color: var(--text-soft);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .db-mini-card strong {
        display: block;
        color: var(--text-main);
        font-size: 1.3rem;
        font-weight: 800;
    }

    .db-empty {
        color: var(--text-soft);
        font-size: 0.95rem;
    }

    @media (max-width: 1100px) {
        .db-hero,
        .db-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .db-account-grid {
            grid-template-columns: 1fr;
        }

        .db-hero h2 {
            font-size: 1.8rem;
        }

        .db-hero-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush

@section('content')
@php
    $accounts = $accounts ?? [];
    $transactions = $transactions ?? [];

    $totalBalance = collect($accounts)->sum(function ($account) {
        return (float) ($account['balance'] ?? 0);
    });

    $recentTransactions = collect($transactions)->take(6);

    $customerName = session('customer.firstName', session('user.username', 'Customer'));
    $customerEmail = session('customer.email', session('user.email', 'Not available'));
@endphp

<div class="db-grid">
    <div class="db-stack">
        <section class="db-card" id="accounts">
            <div class="db-card-head">
                <h3>Accounts Overview</h3>
                <a href="{{ route('transactions') }}">View Transactions</a>
            </div>

            @if(count($accounts))
                <div class="db-account-grid">
                    @foreach($accounts as $account)
                        <div class="db-account-card">
                            <div class="db-account-type">
                                {{ $account['accountType'] ?? 'Account' }}
                            </div>
                            <div class="db-account-balance">
                                {{ number_format((float) ($account['balance'] ?? 0), 2) }} FJD
                            </div>
                            <div class="db-account-meta">
                                Account No: {{ $account['accountNumber'] ?? 'N/A' }}<br>
                                Currency: {{ $account['currency'] ?? 'FJD' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="db-empty">No accounts found for this customer.</div>
            @endif
        </section>

        <section class="db-card">
            <div class="db-card-head">
                <h3>Recent Transactions</h3>
                <a href="{{ route('transactions') }}">Open Full History</a>
            </div>

            @if($recentTransactions->count())
                <div class="db-table-wrap">
                    <table class="db-table">
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
                            @foreach($recentTransactions as $transaction)
                                @php
                                    $status = strtolower($transaction['transactionStatus'] ?? 'completed');
                                    $badgeClass = $status === 'pending'
                                        ? 'db-badge-pending'
                                        : 'db-badge-completed';
                                @endphp
                                <tr>
                                    <td>
                                        {{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $transaction['referenceNumber'] ?? '-' }}</td>
                                    <td>{{ ucfirst($transaction['transferType'] ?? $transaction['transactionType'] ?? 'Transaction') }}</td>
                                    <td class="db-amount">
                                        {{ number_format((float) ($transaction['amount'] ?? 0), 2) }} FJD
                                    </td>
                                    <td>
                                        <span class="db-badge {{ $badgeClass }}">
                                            {{ strtoupper($status === 'completed' ? 'processed' : $status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="db-empty">No recent transactions available.</div>
            @endif
        </section>
    </div>

    <div class="db-stack">
        <section class="db-card" id="profile">
            <div class="db-card-head">
                <h3>Customer Profile</h3>
            </div>

            <div class="db-compact-grid">
                <div class="db-mini-card">
                    <span>Customer Name</span>
                    <strong>{{ $customerName }}</strong>
                </div>

                <div class="db-mini-card">
                    <span>Email</span>
                    <strong style="font-size: 1rem;">{{ $customerEmail }}</strong>
                </div>

                <div class="db-mini-card">
                    <span>Total Portfolio Balance</span>
                    <strong>{{ number_format($totalBalance, 2) }} FJD</strong>
                </div>
            </div>
        </section>

        <section class="db-card">
            <div class="db-card-head">
                <h3>Quick Banking Tools</h3>
            </div>

            <div class="db-feature-list">
                <a href="{{ route('transfer') }}" class="db-feature-item" style="text-decoration:none;">
                    <div class="db-feature-icon">⇄</div>
                    <div class="db-feature-copy">
                        <h4>Transfer Money</h4>
                        <p>Move funds between accounts or to saved beneficiaries quickly and securely.</p>
                    </div>
                </a>

                <a href="{{ route('bill-payment') }}" class="db-feature-item" style="text-decoration:none;">
                    <div class="db-feature-icon">💡</div>
                    <div class="db-feature-copy">
                        <h4>Bill Payments</h4>
                        <p>Pay utility providers and recurring service bills from your banking workspace.</p>
                    </div>
                </a>

                <a href="{{ route('beneficiaries') }}" class="db-feature-item" style="text-decoration:none;">
                    <div class="db-feature-icon">👥</div>
                    <div class="db-feature-copy">
                        <h4>Manage Beneficiaries</h4>
                        <p>Review your saved payees and keep frequently used beneficiary records updated.</p>
                    </div>
                </a>

                <a href="{{ route('scheduled-payments') }}" class="db-feature-item" style="text-decoration:none;">
                    <div class="db-feature-icon">🗓</div>
                    <div class="db-feature-copy">
                        <h4>Scheduled Payments</h4>
                        <p>Track future-dated transfers and recurring payments in one organized place.</p>
                    </div>
                </a>
            </div>
        </section>
    </div>
</div>
@endsection