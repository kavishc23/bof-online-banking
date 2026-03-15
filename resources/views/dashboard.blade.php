@extends('layouts.app')

@php
    $pageTitle = 'Dashboard - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Welcome back, {{ $customer['firstName'] ?? 'Customer' }}</h2>
    <p>View your balances, monitor account activity, manage payments, and keep track of your recent banking transactions in one place.</p>
@endsection

@push('styles')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .left-stack,
    .right-stack {
        display: flex;
    flex-direction: column;
        gap: 24px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .summary-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.07);
        border: 1px solid rgba(226, 232, 240, 0.75);
    }

    body.dark-mode .summary-card,
    body.dark-mode .dashboard-panel,
    body.dark-mode .account-mini-card,
    body.dark-mode .insight-card,
    body.dark-mode .activity-item {
        background: rgba(17, 24, 39, 0.92);
        border-color: rgba(51, 65, 85, 0.85);
    }

    .summary-card h3 {
        margin: 0 0 10px;
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
    }

    .summary-value {
        font-size: 30px;
        font-weight: 800;
        color: #163d7a;
        line-height: 1.1;
    }

    .summary-subtext {
        margin-top: 8px;
        font-size: 13px;
        color: #64748b;
    }

    body.dark-mode .summary-card h3,
    body.dark-mode .summary-subtext,
    body.dark-mode .panel-note,
    body.dark-mode .account-mini-meta,
    body.dark-mode .activity-subtext,
    body.dark-mode .insight-text,
    body.dark-mode .empty-state {
        color: #cbd5e1;
    }

    body.dark-mode .summary-value,
    body.dark-mode .panel-title,
    body.dark-mode .account-mini-title,
    body.dark-mode .activity-title,
    body.dark-mode .insight-title {
        color: #bfdbfe;
    }

    .featured-card {
        background: linear-gradient(135deg, #0b2147 0%, #163d7a 55%, #2563eb 100%);
        color: white;
        border-radius: 24px;
        padding: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 43, 91, 0.24);
    }

    .featured-card::before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
        top: -120px;
        right: -100px;
    }

    .featured-card::after {
        content: "";
        position: absolute;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        bottom: -70px;
        right: 40px;
    }

    .featured-top {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .bank-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 700;
        color: #e0ecff;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.15);
    }

    .featured-label {
        margin-top: 18px;
        font-size: 13px;
        color: #dbeafe;
        position: relative;
        z-index: 1;
    }

    .featured-balance {
        margin-top: 8px;
        font-size: 38px;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }

    .featured-number {
        margin-top: 28px;
        font-size: 22px;
        letter-spacing: 3px;
        font-weight: 700;
        color: #f8fbff;
        position: relative;
        z-index: 1;
    }

    .featured-bottom {
        margin-top: 26px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .featured-meta-label {
        font-size: 12px;
        color: #dbeafe;
        margin-bottom: 6px;
    }

    .featured-meta-value {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
    }

    .dashboard-panel {
        background: white;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.07);
        border: 1px solid rgba(226, 232, 240, 0.75);
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
    }

    .panel-title {
        font-size: 22px;
        font-weight: 800;
        color: #163d7a;
        margin: 0;
    }

    .panel-note {
        font-size: 13px;
        color: #64748b;
        margin-top: 4px;
    }

    .panel-link {
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        color: #2563eb;
        white-space: nowrap;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
    }

    .quick-action-card {
        text-decoration: none;
        background: linear-gradient(135deg, #f8fbff, #eef5ff);
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 18px;
        color: #163d7a;
        transition: 0.2s ease;
        min-height: 115px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    body.dark-mode .quick-action-card {
        background: linear-gradient(135deg, #0f172a, #111827);
        border-color: #334155;
        color: #bfdbfe;
    }

    .quick-action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.12);
    }

    .quick-icon {
        font-size: 24px;
    }

    .quick-title {
        font-size: 15px;
        font-weight: 800;
    }

    .quick-text {
        font-size: 13px;
        color: #64748b;
        line-height: 1.45;
    }

    body.dark-mode .quick-text {
        color: #cbd5e1;
    }

    .accounts-mini-grid {
        display: grid;
        gap: 14px;
    }

    .account-mini-card {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .account-mini-title {
        font-size: 15px;
        font-weight: 800;
        color: #163d7a;
        margin-bottom: 6px;
    }

    .account-mini-meta {
        font-size: 13px;
        color: #64748b;
        line-height: 1.45;
    }

    .account-mini-balance {
        font-size: 22px;
        font-weight: 800;
        color: #163d7a;
        white-space: nowrap;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .activity-item {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px 18px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .activity-left {
        flex: 1;
    }

    .activity-title {
        font-size: 15px;
        font-weight: 800;
        color: #163d7a;
        margin-bottom: 6px;
    }

    .activity-subtext {
        font-size: 13px;
        color: #64748b;
        line-height: 1.45;
    }

    .activity-amount {
        font-size: 16px;
        font-weight: 800;
        white-space: nowrap;
    }

    .amount-in {
        color: #166534;
    }

    .amount-out {
        color: #b91c1c;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
        white-space: nowrap;
    }

    .badge-completed {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-internal {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-local {
        background: #ede9fe;
        color: #6d28d9;
    }

    .badge-bill {
        background: #fce7f3;
        color: #be185d;
    }

    .badge-deposit {
        background: #dcfce7;
        color: #166534;
    }

    .insight-list {
        display: grid;
        gap: 14px;
    }

    .insight-card {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px 18px;
    }

    .insight-title {
        font-size: 15px;
        font-weight: 800;
        color: #163d7a;
        margin-bottom: 8px;
    }

    .insight-text {
        font-size: 13px;
        color: #64748b;
        line-height: 1.55;
    }

    .empty-state {
        color: #64748b;
        font-size: 14px;
    }

    @media (max-width: 1100px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .featured-bottom {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    @php
        $accounts = $customer['accounts'] ?? [];
        $primaryAccount = $accounts[0] ?? null;
        $recentTransactions = array_slice($transactions ?? [], 0, 5);

        $totalBalance = 0;
        $totalIncoming = 0;
        $totalOutgoing = 0;
        $billPaymentCount = 0;
        $transferCount = 0;

        foreach ($accounts as $account) {
            $totalBalance += (float)($account['balance'] ?? 0);
        }

        foreach (($transactions ?? []) as $transaction) {
            $transactionType = strtolower($transaction['transactionType'] ?? '');
            $transferType = strtolower($transaction['transferType'] ?? '');
            $amount = (float)($transaction['amount'] ?? 0);

            if ($transactionType === 'deposit' || $transferType === 'deposit') {
                $totalIncoming += $amount;
            } else {
                $totalOutgoing += $amount;
            }

            if ($transferType === 'billpayment') {
                $billPaymentCount++;
            }

            if (in_array($transferType, ['internal', 'localbank'])) {
                $transferCount++;
            }
        }

        $netFlow = $totalIncoming - $totalOutgoing;
    @endphp

    @if($customer)
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Available Balance</h3>
                <div class="summary-value">${{ number_format($totalBalance, 2) }}</div>
                <div class="summary-subtext">Across all linked accounts</div>
            </div>

            <div class="summary-card">
                <h3>Money In</h3>
                <div class="summary-value">${{ number_format($totalIncoming, 2) }}</div>
                <div class="summary-subtext">Deposits and incoming transfers</div>
            </div>

            <div class="summary-card">
                <h3>Money Out</h3>
                <div class="summary-value">${{ number_format($totalOutgoing, 2) }}</div>
                <div class="summary-subtext">Transfers and bill payments</div>
            </div>

            <div class="summary-card">
                <h3>Net Activity</h3>
                <div class="summary-value">{{ $netFlow >= 0 ? '+' : '-' }}${{ number_format(abs($netFlow), 2) }}</div>
                <div class="summary-subtext">Incoming minus outgoing</div>
            </div>

            <div class="summary-card">
                <h3>Total Transfers</h3>
                <div class="summary-value">{{ $transferCount }}</div>
                <div class="summary-subtext">Internal and local bank transfers</div>
            </div>

            <div class="summary-card">
                <h3>Bill Payments</h3>
                <div class="summary-value">{{ $billPaymentCount }}</div>
                <div class="summary-subtext">Completed bill payment activity</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="left-stack">
                @if($primaryAccount)
                    <div class="featured-card">
                        <div class="featured-top">
                            <div class="bank-chip">
                                <span class="live-dot"></span>
                                <span>Secure Banking Session</span>
                            </div>
                            <div class="bank-chip">Bank of Fiji</div>
                        </div>

                        <div class="featured-label">Primary Account Balance</div>
                        <div class="featured-balance">${{ number_format((float)($primaryAccount['balance'] ?? 0), 2) }}</div>

                        <div class="featured-number">
                            **** **** **** {{ substr((string)($primaryAccount['accountNumber'] ?? '0000'), -4) }}
                        </div>

                        <div class="featured-bottom">
                            <div>
                                <div class="featured-meta-label">Account Holder</div>
                                <div class="featured-meta-value">
                                    {{ strtoupper(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) }}
                                </div>
                            </div>

                            <div>
                                <div class="featured-meta-label">Account Type</div>
                                <div class="featured-meta-value">{{ $primaryAccount['accountType'] ?? 'Primary Account' }}</div>
                            </div>

                            <div>
                                <div class="featured-meta-label">Interest Rate</div>
                                <div class="featured-meta-value">{{ $primaryAccount['interestRate'] ?? '0' }}%</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="dashboard-panel">
                    <div class="panel-header">
                        <div>
                            <h3 class="panel-title">Quick Actions</h3>
                            <div class="panel-note">Common banking actions available from your dashboard.</div>
                        </div>
                    </div>

                    <div class="quick-actions-grid">
                        <a class="quick-action-card" href="{{ route('transfer') }}">
                            <div class="quick-icon">🔁</div>
                            <div class="quick-title">Transfer Money</div>
                            <div class="quick-text">Send funds to another BoF account or local bank.</div>
                        </a>

                        <a class="quick-action-card" href="{{ route('bill-payment') }}">
                            <div class="quick-icon">💡</div>
                            <div class="quick-title">Pay Bills</div>
                            <div class="quick-text">Settle utility, telecom, and other bill payments.</div>
                        </a>

                        <a class="quick-action-card" href="{{ route('transactions') }}">
                            <div class="quick-icon">📄</div>
                            <div class="quick-title">View Transactions</div>
                            <div class="quick-text">Review detailed account activity and export records.</div>
                        </a>

                        <a class="quick-action-card" href="#accounts">
                            <div class="quick-icon">💳</div>
                            <div class="quick-title">View Accounts</div>
                            <div class="quick-text">Check balances, interest rates, and linked accounts.</div>
                        </a>
                    </div>
                </div>

                <div class="dashboard-panel" id="accounts">
                    <div class="panel-header">
                        <div>
                            <h3 class="panel-title">Your Accounts</h3>
                            <div class="panel-note">A quick snapshot of your linked banking accounts.</div>
                        </div>
                    </div>

                    @if(count($accounts) > 0)
                        <div class="accounts-mini-grid">
                            @foreach($accounts as $account)
                                <div class="account-mini-card">
                                    <div>
                                        <div class="account-mini-title">{{ $account['accountType'] ?? 'Account' }}</div>
                                        <div class="account-mini-meta">
                                            Account Number: {{ $account['accountNumber'] ?? '-' }}<br>
                                            Interest Rate: {{ $account['interestRate'] ?? '0' }}%
                                        </div>
                                    </div>

                                    <div class="account-mini-balance">
                                        ${{ number_format((float)($account['balance'] ?? 0), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="empty-state">No linked accounts found.</p>
                    @endif
                </div>
            </div>

            <div class="right-stack">
                <div class="dashboard-panel" id="transactions">
                    <div class="panel-header">
                        <div>
                            <h3 class="panel-title">Recent Activity</h3>
                            <div class="panel-note">Showing your latest 5 banking transactions.</div>
                        </div>
                        <a class="panel-link" href="{{ route('transactions') }}">View all</a>
                    </div>

                    @if(count($recentTransactions) > 0)
                        <div class="activity-list">
                            @foreach($recentTransactions as $transaction)
                                @php
                                    $transactionType = strtolower($transaction['transactionType'] ?? '');
                                    $transferType = strtolower($transaction['transferType'] ?? '');
                                    $status = strtolower($transaction['transactionStatus'] ?? 'completed');
                                    $amount = (float)($transaction['amount'] ?? 0);

                                    $isIncoming = $transactionType === 'deposit' || $transferType === 'deposit';
                                    $amountClass = $isIncoming ? 'amount-in' : 'amount-out';

                                    $modeLabel = $transaction['transferType'] ?? $transaction['transactionType'] ?? 'Transaction';
                                @endphp

                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-title">{{ $transaction['description'] ?? 'Transaction' }}</div>
                                        <div class="activity-subtext">
                                            Ref: {{ $transaction['referenceNumber'] ?? '-' }}<br>
                                            {{ $transaction['destinationInstitution'] ?? 'BoF' }} ·
                                            {{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y, h:i A') : '-' }}
                                        </div>

                                        <div style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap;">
                                            @if($transferType === 'internal')
                                                <span class="badge badge-internal">Internal</span>
                                            @elseif($transferType === 'localbank')
                                                <span class="badge badge-local">Local Bank</span>
                                            @elseif($transferType === 'billpayment')
                                                <span class="badge badge-bill">Bill Payment</span>
                                            @elseif($transferType === 'deposit')
                                                <span class="badge badge-deposit">Deposit</span>
                                            @else
                                                <span class="badge badge-internal">{{ $modeLabel }}</span>
                                            @endif

                                            @if($status === 'completed')
                                                <span class="badge badge-completed">Completed</span>
                                            @elseif($status === 'pending')
                                                <span class="badge badge-pending">Pending</span>
                                            @elseif($status === 'failed')
                                                <span class="badge badge-failed">Failed</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="activity-amount {{ $amountClass }}">
                                        {{ $isIncoming ? '+' : '-' }}${{ number_format($amount, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="empty-state">No recent transactions found.</p>
                    @endif
                </div>

                <div class="dashboard-panel" id="profile">
                    <div class="panel-header">
                        <div>
                            <h3 class="panel-title">Customer Profile</h3>
                            <div class="panel-note">Basic profile information linked to your banking account.</div>
                        </div>
                    </div>

                    <div class="insight-list">
                        <div class="insight-card">
                            <div class="insight-title">Full Name</div>
                            <div class="insight-text">{{ $customer['firstName'] ?? '' }} {{ $customer['lastName'] ?? '' }}</div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-title">Email Address</div>
                            <div class="insight-text">{{ $customer['email'] ?? '-' }}</div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-title">Phone Number</div>
                            <div class="insight-text">{{ $customer['phone'] ?? '-' }}</div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-title">TIN / Reference</div>
                            <div class="insight-text">{{ $customer['tin'] ?? '-' }}</div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-title">Residency Status</div>
                            <div class="insight-text">{{ $customer['residencyStatus'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-panel">
                    <div class="panel-header">
                        <div>
                            <h3 class="panel-title">Account Insights</h3>
                            <div class="panel-note">Helpful banking information based on your current activity.</div>
                        </div>
                    </div>

                    <div class="insight-list">
                        <div class="insight-card">
                            <div class="insight-title">Spending Overview</div>
                            <div class="insight-text">
                                Your total outgoing activity currently stands at <strong>${{ number_format($totalOutgoing, 2) }}</strong>,
                                while total incoming funds stand at <strong>${{ number_format($totalIncoming, 2) }}</strong>.
                            </div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-title">Most Useful Next Action</div>
                            <div class="insight-text">
                                Use <strong>Transfer Money</strong> for account-to-account movement, or <strong>Pay Bills</strong>
                                to settle registered billers directly from your account.
                            </div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-title">Demo Banking Reminder</div>
                            <div class="insight-text">
                                This portal currently reflects demo-mode banking activity. Transfers and bill payments are processed instantly for testing and presentation purposes.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="dashboard-panel">
            <p class="empty-state">No linked customer profile found for this login.</p>
        </div>
    @endif
@endsection