<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoF Online Banking Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef3f8;
            color: #1f2937;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0f2b5b, #163d7a);
            color: white;
            padding: 28px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand h1 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }

        .brand p {
            margin-top: 8px;
            color: #dbeafe;
            font-size: 14px;
        }

        .menu {
            margin-top: 35px;
        }

        .menu a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 12px 14px;
            margin-bottom: 10px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            background: rgba(255,255,255,0.08);
            transition: 0.2s ease;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.18);
        }

        .menu a.active {
            background: rgba(255,255,255,0.2);
        }

        .logout-form {
            margin-top: 30px;
        }

        .logout-btn {
            width: 100%;
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 14px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #b91c1c;
        }

        .main {
            flex: 1;
            padding: 30px;
        }

        .top-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .top-card h2 {
            margin: 0 0 10px;
            color: #163d7a;
            font-size: 30px;
        }

        .top-card p {
            margin: 0;
            color: #6b7280;
        }

        .success-box {
            background: #dcfce7;
            color: #166534;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }

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

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            color: #6b7280;
            font-size: 15px;
        }

        .quick-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .action-btn {
            text-decoration: none;
            background: #1d4ed8;
            color: white;
            padding: 11px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }

        .action-btn:hover {
            background: #1e40af;
        }

        .section-note {
            color: #6b7280;
            font-size: 13px;
            margin-top: -8px;
            margin-bottom: 18px;
        }

        @media (max-width: 960px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-radius: 0;
            }

            .main {
                padding: 20px;
            }

            .featured-card .card-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div>
            <div class="brand">
                <h1>BoF</h1>
                <p>Online Banking Portal</p>
            </div>

            <nav class="menu">
                <a href="{{ route('dashboard') }}" class="active">Dashboard Overview</a>
                <a href="#profile">Customer Profile</a>
                <a href="#accounts">Accounts</a>
                <a href="{{ route('transactions') }}">Transactions</a>
                <a href="{{ route('transfer') }}">Transfer Money</a>
            </nav>
        </div>

        <form class="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">Logout</button>
        </form>
    </aside>

    <main class="main">
        <div class="top-card" id="overview">
            <h2>Welcome back, {{ $customer['firstName'] ?? 'Customer' }}</h2>
            <p>Manage your accounts, view transaction history, and perform transfers securely.</p>

            <div class="quick-actions">
                <a class="action-btn" href="#accounts">View Accounts</a>
                <a class="action-btn" href="{{ route('transactions') }}">View Transactions</a>
                <a class="action-btn" href="{{ route('transfer') }}">Transfer Money</a>
            </div>
        </div>

        @if(session('success'))
            <div class="success-box">
                {{ session('success') }}
            </div>
        @endif

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
                    <div class="small">Across all linked accounts</div>
                </div>

                <div class="summary-card">
                    <h3>Total Accounts</h3>
                    <div class="value">{{ $accountCount }}</div>
                    <div class="small">Active banking accounts</div>
                </div>

                <div class="summary-card">
                    <h3>Total Transactions</h3>
                    <div class="value">{{ $transactionCount }}</div>
                    <div class="small">Visible transaction history</div>
                </div>
            </div>

            <section class="section" id="profile">
                <h3>Customer Profile</h3>

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

            <section class="section" id="accounts">
                <h3>Your Accounts</h3>
                <p class="section-note">Your main bank card and linked banking accounts are shown below.</p>

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

            <section class="section" id="transactions">
                <h3>Recent Transactions</h3>
                <p class="section-note">Showing your latest 5 transactions. View the full list from the Transactions page.</p>

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
                                        <td>
                                            @php
                                                $status = strtolower($transaction['status'] ?? 'completed');
                                            @endphp

                                            @if($status === 'completed')
                                                <span class="badge badge-completed">Completed</span>
                                            @elseif($status === 'pending')
                                                <span class="badge badge-pending">Pending</span>
                                            @elseif($status === 'failed')
                                                <span class="badge badge-failed">Failed</span>
                                            @else
                                                <span class="badge badge-completed">{{ $transaction['status'] ?? 'Completed' }}</span>
                                            @endif
                                        </td>
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
    </main>
</div>

</body>
</html>