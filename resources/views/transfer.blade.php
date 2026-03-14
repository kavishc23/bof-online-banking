<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money - BoF Online Banking</title>
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

        .back-link {
            display: inline-block;
            text-decoration: none;
            color: white;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            background: rgba(255,255,255,0.08);
            transition: 0.2s ease;
        }

        .back-link:hover {
            background: rgba(255,255,255,0.18);
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

        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
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

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #1f2937;
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

        .error-box {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .validation-box {
            background: #fff7ed;
            color: #9a3412;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
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

        .tips-box p:last-child {
            margin-bottom: 0;
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

            .content-grid {
                grid-template-columns: 1fr;
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
                <a href="{{ route('dashboard') }}#transactions">Transactions</a>
                <a href="{{ route('transfer') }}" class="active">Transfer Money</a>
            </nav>
        </div>

        <a class="back-link" href="{{ route('dashboard') }}">← Back to Dashboard</a>
    </aside>

    <main class="main">
        <div class="top-card">
            <h2>Transfer Money</h2>
            <p>Move funds securely between accounts by entering the destination account number and amount.</p>
        </div>

        <div class="content-grid">
            <section class="panel">
                <h3>Transfer Details</h3>

                @if(session('error'))
                    <div class="error-box">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="validation-box">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('transfer.submit') }}">
                    @csrf

                    <label for="from_account_id">From Account</label>
                    <select name="from_account_id" id="from_account_id" required>
                        @foreach($customer['accounts'] ?? [] as $account)
                            <option value="{{ $account['id'] }}" {{ old('from_account_id') == $account['id'] ? 'selected' : '' }}>
                                {{ $account['accountNumber'] }} - {{ $account['accountType'] }} - ${{ number_format((float)($account['balance'] ?? 0), 2) }}
                            </option>
                        @endforeach
                    </select>

                    <label for="to_account_number">Destination Account Number</label>
                    <input
                        type="text"
                        id="to_account_number"
                        name="to_account_number"
                        value="{{ old('to_account_number') }}"
                        placeholder="Enter destination account number"
                        required
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

                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Add a note for this transfer"
                    >{{ old('description') }}</textarea>

                    <button type="submit" class="submit-btn">Send Transfer</button>
                </form>
            </section>

            <section>
                @php
                    $firstAccount = $customer['accounts'][0] ?? null;
                @endphp

                @if($firstAccount)
                    <div class="info-card">
                        <h4>Selected Banking Profile</h4>
                        <div class="balance">${{ number_format((float)($firstAccount['balance'] ?? 0), 2) }}</div>
                        <div class="meta">Account Number: {{ $firstAccount['accountNumber'] ?? '' }}</div>
                        <div class="meta">Account Type: {{ $firstAccount['accountType'] ?? '' }}</div>
                    </div>
                @endif

                <div class="tips-box">
                    <h4>Transfer Tips</h4>
                    <p>Double-check the destination account number before submitting your transfer.</p>
                    <p>Ensure your selected account has enough balance to complete the transaction.</p>
                    <p>All completed transfers will appear in your transaction history on the dashboard.</p>
                </div>
            </section>
        </div>
    </main>
</div>

</body>
</html>