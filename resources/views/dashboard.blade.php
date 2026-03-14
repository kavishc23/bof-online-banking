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

        .navbar {
            background: linear-gradient(90deg, #0f2b5b, #163d7a);
            color: white;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
        }

        .navbar h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }

        .navbar p {
            margin: 4px 0 0;
            font-size: 14px;
            color: #dbeafe;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #b91c1c;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: white;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .welcome-card h2 {
            margin-top: 0;
            color: #163d7a;
            font-size: 30px;
        }

        .welcome-card p {
            margin-bottom: 0;
            color: #6b7280;
            font-size: 15px;
        }

        .quick-links {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin: 20px 0 28px;
        }

        .quick-links a {
            text-decoration: none;
            background: #1d4ed8;
            color: white;
            padding: 11px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .quick-links a:hover {
            background: #1e40af;
        }

        .account-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .account-card {
            background: linear-gradient(135deg, #163d7a, #1d4ed8);
            color: white;
            padding: 22px;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
        }

        .account-card h3 {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 500;
            color: #dbeafe;
        }

        .account-card .amount {
            font-size: 30px;
            font-weight: bold;
            margin: 12px 0;
        }

        .account-card .small {
            font-size: 13px;
            color: #dbeafe;
        }

        .section-title {
            font-size: 28px;
            color: #163d7a;
            margin-bottom: 16px;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        .panel {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .panel h3 {
            margin-top: 0;
            margin-bottom: 18px;
            color: #163d7a;
            font-size: 24px;
        }

        .profile-item {
            margin-bottom: 14px;
            font-size: 15px;
        }

        .profile-item span {
            font-weight: bold;
            color: #111827;
        }

        .account-box {
            background: #f8fbff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 14px;
        }

        .account-box p {
            margin: 8px 0;
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

        @media (max-width: 900px) {
            .two-column {
                grid-template-columns: 1fr;
            }

            .navbar {
                padding: 16px 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .navbar form {
                width: 100%;
            }

            .logout-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div>
            <h1>BoF Online Banking</h1>
            <p>Secure banking at your fingertips</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">Logout</button>
        </form>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Welcome back, {{ $customer['firstName'] ?? 'Customer' }}</h2>
            <p>Here is an overview of your banking profile, accounts, and recent transactions.</p>
        </div>

        <div class="quick-links">
            <a href="#profile">Profile</a>
            <a href="#accounts">Accounts</a>
            <a href="#transactions">Transactions</a>
        </div>

        @if($customer)
            <div class="account-grid">
                @forelse($customer['accounts'] ?? [] as $account)
                    <div class="account-card">
                        <h3>{{ $account['accountType'] ?? 'Account' }}</h3>
                        <div class="amount">${{ number_format((float)($account['balance'] ?? 0), 2) }}</div>
                        <div class="small">Account Number: {{ $account['accountNumber'] ?? '' }}</div>
                    </div>
                @empty
                    <div class="account-card">
                        <h3>No Accounts</h3>
                        <div class="amount">$0.00</div>
                        <div class="small">No active accounts linked yet</div>
                    </div>
                @endforelse
            </div>

            <div class="two-column">
                <div class="panel" id="profile">
                    <h3>Customer Profile</h3>
                    <div class="profile-item"><span>Name:</span> {{ $customer['firstName'] ?? '' }} {{ $customer['lastName'] ?? '' }}</div>
                    <div class="profile-item"><span>Email:</span> {{ $customer['email'] ?? '' }}</div>
                    <div class="profile-item"><span>Phone:</span> {{ $customer['phone'] ?? '' }}</div>
                    <div class="profile-item"><span>TIN:</span> {{ $customer['tin'] ?? '' }}</div>
                    <div class="profile-item"><span>Residency:</span> {{ $customer['residencyStatus'] ?? '' }}</div>
                </div>

                <div class="panel" id="accounts">
                    <h3>Accounts</h3>
                    @forelse($customer['accounts'] ?? [] as $account)
                        <div class="account-box">
                            <p><strong>Account Number:</strong> {{ $account['accountNumber'] ?? '' }}</p>
                            <p><strong>Account Type:</strong> {{ $account['accountType'] ?? '' }}</p>
                            <p><strong>Balance:</strong> ${{ number_format((float)($account['balance'] ?? 0), 2) }}</p>
                            <p><strong>Interest Rate:</strong> {{ $account['interestRate'] ?? '' }}%</p>
                        </div>
                    @empty
                        <p class="empty-state">No accounts linked yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel" id="transactions">
                <h3>Recent Transactions</h3>

                @if(count($transactions) > 0)
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
                                @foreach($transactions as $transaction)
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
                                        <td>{{ $transaction['transactionDate'] ?? '' }}</td>
                                        <td>{{ $transaction['description'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="empty-state">No transactions found for your account.</p>
                @endif
            </div>
        @else
            <div class="panel">
                <p class="empty-state">No linked customer profile found for this login.</p>
            </div>
        @endif
    </div>

</body>
</html>