@extends('layouts.app')

@php
    $pageTitle = 'Dashboard - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Welcome back, {{ $customer['firstName'] ?? 'Customer' }}</h2>
    <p>Your personal banking dashboard gives you a quick view of balances, accounts, and recent activity.</p>

    <div class="quick-actions">
        <a class="action-btn" href="#accounts">View Accounts</a>
        <a class="action-btn" href="{{ route('transactions') }}">View Transactions</a>
        <a class="action-btn" href="{{ route('transfer') }}">Transfer Money</a>
        <a class="action-btn" href="{{ route('bill-payment') }}">Pay Bill</a>
    </div>
@endsection

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .summary-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    .summary-card h3 {
        margin: 0 0 10px;
        color: #6b7280;
        font-size: 15px;
        font-weight: 600;
    }

    .summary-card .value {
        font-size: 30px;
        font-weight: bold;
        color: #163d7a;
    }

    .summary-card .small {
        margin-top: 8px;
        color: #6b7280;
        font-size: 13px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .section {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }

    .section h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .profile-item {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 14px;
    }

    .profile-item .label {
        display: block;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .profile-item .value {
        font-weight: bold;
        color: #111827;
    }

    .featured-card {
        background: linear-gradient(135deg, #0f2b5b, #1d4ed8);
        color: white;
        border-radius: 22px;
        padding: 28px;
        box-shadow: 0 10px 28px rgba(15, 43, 91, 0.28);
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }

    .featured-card::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
        top: -40px;
        right: -40px;
    }

    .featured-card .bank-name {
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .featured-card .card-type {
        margin-top: 6px;
        font-size: 13px;
        color: #dbeafe;
    }

    .featured-card .card-number {
        margin-top: 34px;
        font-size: 24px;
        letter-spacing: 3px;
        font-weight: bold;
    }

    .featured-card .card-row {
        display: flex;
        justify-content: space-between;
        align-items: end;
        margin-top: 28px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .featured-card .label {
        font-size: 12px;
        color: #dbeafe;
        margin-bottom: 6px;
    }

    .featured-card .value {
        font-size: 15px;
        font-weight: bold;
    }

    .featured-card .big-balance {
        font-size: 30px;
        font-weight: bold;
    }

    .accounts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
        gap: 18px;
    }

    .account-card {
        background: linear-gradient(135deg, #163d7a, #1d4ed8);
        color: white;
        padding: 22px;
        border-radius: 18px;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
    }

    .account-card h4 {
        margin: 0 0 10px;
        font-size: 16px;
        color: #dbeafe;
        font-weight: 500;
    }

    .account-card .balance {
        font-size: 28px;
        font-weight: bold;
        margin: 12px 0;
    }

    .account-card .meta {
        font-size: 14px;
        color: #dbeafe;
        margin-top: 8px;
    }

    .mini-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .mini-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fafcff;
    }

    .mini-item-left {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .mini-item-title {
        font-weight: 700;
        color: #163d7a;
        font-size: 14px;
    }

    .mini-item-subtitle {
        font-size: 12px;
        color: #6b7280;
    }

    .mini-item-amount {
        font-weight: bold;
        color: #111827;
        font-size: 14px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }

    th, td {
        padding: 14px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        font-size: 14px;
    }

    th {
        background: #eaf2ff;
        color: #163d7a;
        font-size: 14px;
    }

    tr:hover {
        background: #f9fbff;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }

    .badge-completed {
        background: #dcfce7;
        color: #166534;
    }

    .empty-state {
        color: #6b7280;
        font-size: 15px;
    }

    @media (max-width: 960px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    @if($customer)
        @php
            $totalBalance = 0;
            $accountCount = count($customer['accounts'] ?? []);
            $transactionCount = count($transactions ?? []);
            $recentTransactions = array_slice($transactions ?? [], 0, 5);
            $primaryAccount = $customer['accounts'][0] ?? null;

            foreach (($customer['accounts'] ?? []) as $account) {
                $totalBalance += (float)($account['balance'] ?? 0);
            }
        @endphp

        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Balance</h3>
                <div class="value">${{ number_format($totalBalance, 2) }}</div>
                <div class="small">Combined balance across all linked accounts</div>
            </div>

            <div class="summary-card">
                <h3>Active Accounts</h3>
                <div class="value">{{ $accountCount }}</div>
                <div class="small">Accounts currently linked to your profile</div>
            </div>

            <div class="summary-card">
                <h3>Transaction Records</h3>
                <div class="value">{{ $transactionCount }}</div>
                <div class="small">Recent activity available in your banking history</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <section class="section" id="accounts">
                <h3>Primary Account</h3>
                <p class="section-note">A quick card-style view of your main account.</p>

                @if($primaryAccount)
                    <div class="featured-card">
                        <div class="bank-name">Bank of Fiji</div>
                        <div class="card-type">{{ $primaryAccount['accountType'] ?? 'Primary Account' }}</div>

                        <div class="card-number">
                            **** **** **** {{ substr((string)($primaryAccount['accountNumber'] ?? '0000'), -4) }}
                        </div>

                        <div class="card-row">
                            <div>
                                <div class="label">Card Holder</div>
                                <div class="value">{{ strtoupper(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) }}</div>
                            </div>

                            <div>
                                <div class="label">Available Balance</div>
                                <div class="big-balance">${{ number_format((float)($primaryAccount['balance'] ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="accounts-grid">
                    @forelse($customer['accounts'] ?? [] as $account)
                        <div class="account-card">
                            <h4>{{ $account['accountType'] ?? 'Account' }}</h4>
                            <div class="balance">${{ number_format((float)($account['balance'] ?? 0), 2) }}</div>
                            <div class="meta">Account Number: {{ $account['accountNumber'] ?? '' }}</div>
                            <div class="meta">Interest Rate: {{ $account['interestRate'] ?? '' }}%</div>
                        </div>
                    @empty
                        <p class="empty-state">No accounts linked yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="section">
                <h3>Quick Financial Snapshot</h3>
                <p class="section-note">A compact overview of your latest account activity.</p>

                <div class="mini-list">
                    @if($primaryAccount)
                        <div class="mini-item">
                            <div class="mini-item-left">
                                <div class="mini-item-title">Main Account</div>
                                <div class="mini-item-subtitle">{{ $primaryAccount['accountNumber'] ?? '' }} • {{ $primaryAccount['accountType'] ?? '' }}</div>
                            </div>
                            <div class="mini-item-amount">${{ number_format((float)($primaryAccount['balance'] ?? 0), 2) }}</div>
                        </div>
                    @endif

                    <div class="mini-item">
                        <div class="mini-item-left">
                            <div class="mini-item-title">Profile Name</div>
                            <div class="mini-item-subtitle">Registered banking customer</div>
                        </div>
                        <div class="mini-item-amount">{{ $customer['firstName'] ?? '' }}</div>
                    </div>

                    <div class="mini-item">
                        <div class="mini-item-left">
                            <div class="mini-item-title">Recent Activity</div>
                            <div class="mini-item-subtitle">Latest 5 records shown below</div>
                        </div>
                        <div class="mini-item-amount">{{ count($recentTransactions) }}</div>
                    </div>

                    <div class="mini-item">
                        <div class="mini-item-left">
                            <div class="mini-item-title">Banking Actions</div>
                            <div class="mini-item-subtitle">Transfer and bill payment available</div>
                        </div>
                        <div class="mini-item-amount">Live</div>
                    </div>
                </div>
            </section>
        </div>

        <section class="section" id="profile">
            <h3>Customer Profile</h3>
            <p class="section-note">Your registered customer details and profile information.</p>

            <div class="profile-grid">
                <div class="profile-item">
                    <span class="label">Full Name</span>
                    <div class="value">{{ $customer['firstName'] ?? '' }} {{ $customer['lastName'] ?? '' }}</div>
                </div>

                <div class="profile-item">
                    <span class="label">Email Address</span>
                    <div class="value">{{ $customer['email'] ?? '' }}</div>
                </div>

                <div class="profile-item">
                    <span class="label">Phone Number</span>
                    <div class="value">{{ $customer['phone'] ?? '' }}</div>
                </div>

                <div class="profile-item">
                    <span class="label">TIN</span>
                    <div class="value">{{ $customer['tin'] ?? '' }}</div>
                </div>

                <div class="profile-item">
                    <span class="label">Residency Status</span>
                    <div class="value">{{ $customer['residencyStatus'] ?? '' }}</div>
                </div>

                <div class="profile-item">
                    <span class="label">Address</span>
                    <div class="value">{{ $customer['address'] ?? '' }}</div>
                </div>
            </div>
        </section>

        <section class="section" id="transactions">
            <h3>Recent Transactions</h3>
            <p class="section-note">Showing your latest 5 transactions. Use the Transactions page for the full list.</p>

            @if(count($recentTransactions) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction['referenceNumber'] ?? '' }}</td>
                                    <td>{{ $transaction['transactionType'] ?? '' }}</td>
                                    <td>${{ number_format((float)($transaction['amount'] ?? 0), 2) }}</td>
                                    <td><span class="badge badge-completed">Completed</span></td>
                                    <td>
                                        @if(!empty($transaction['transactionDate']))
                                            {{ \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $transaction['description'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty-state">No transactions found for your account.</p>
            @endif
        </section>
    @else
        <section class="section">
            <p class="empty-state">No linked customer profile found for this login.</p>
        </section>
    @endif
@endsection