<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - BoF Online Banking</title>
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            margin: 0 0 10px;
            color: #6b7280;
            font-size: 15px;
            font-weight: 600;
        }

        .stat-card .value {
            font-size: 30px;
            font-weight: bold;
            color: #163d7a;
        }

        .section {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .section h3 {
            margin-top: 0;
            margin-bottom: 18px;
            color: #163d7a;
            font-size: 24px;
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

        .amount-positive {
            color: #166534;
            font-weight: bold;
        }

        .amount-negative {
            color: #b91c1c;
            font-weight: bold;
        }

        .empty-state {
            color: #6b7280;
            font-size: 15px;
        }

        @media (max-width: 960px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .main {
                padding: 20px;
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
                <a href="{{ route('dashboard') }}">Dashboard Overview</a>
                <a href="{{ route('dashboard') }}#profile">Customer Profile</a>
                <a href="{{ route('dashboard') }}#accounts">Accounts</a>
                <a href="{{ route('transactions') }}" class="active">Transactions</a>
                <a href="{{ route('transfer') }}">Transfer Money</a>
            </nav>
        </div>

        <form class="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">Logout</button>
        </form>
    </aside>

    <main class="main">
        <div class="top-card">
            <h2>Transaction History</h2>
            <p>View all recent activity across your linked accounts.</p>
        </div>

        @php
            $transactionCount = count($transactions ?? []);
            $totalIn = 0;
            $totalOut = 0;

            foreach (($transactions ?? []) as $transaction) {
                $type = strtolower($transaction['transactionType'] ?? '');
                $amount = (float)($transaction['amount'] ?? 0);

                if (in_array($type, ['deposit'])) {
                    $totalIn += $amount;
                } else {
                    $totalOut += $amount;
                }
            }
        @endphp

        <div class="stats-row">
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <div class="value">{{ $transactionCount }}</div>
            </div>

            <div class="stat-card">
                <h3>Total Incoming</h3>
                <div class="value">${{ number_format($totalIn, 2) }}</div>
            </div>

            <div class="stat-card">
                <h3>Total Outgoing</h3>
                <div class="value">${{ number_format($totalOut, 2) }}</div>
            </div>
        </div>

        <section class="section">
            <h3>All Transactions</h3>

            @if(count($transactions) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                @php
                                    $type = strtolower($transaction['transactionType'] ?? '');
                                    $amountClass = in_array($type, ['deposit']) ? 'amount-positive' : 'amount-negative';
                                @endphp
                                <tr>
                                    <td>{{ $transaction['referenceNumber'] ?? '' }}</td>
                                    <td>{{ $transaction['account']['accountNumber'] ?? '-' }}</td>
                                    <td>{{ $transaction['transactionType'] ?? '' }}</td>
                                    <td class="{{ $amountClass }}">
                                        ${{ number_format((float)($transaction['amount'] ?? 0), 2) }}
                                    </td>
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
    </main>
</div>

</body>
</html>