<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoF Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            padding: 30px;
        }

        .card {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #1d3557;
        }

        .account-box {
            background: #f8fafc;
            border: 1px solid #dbeafe;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        button {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>

    <div class="card">
        <h1>Welcome to BoF Online Banking</h1>

        @if($customer)
            <h2>Customer Information</h2>
            <p><strong>Name:</strong> {{ $customer['firstName'] ?? '' }} {{ $customer['lastName'] ?? '' }}</p>
            <p><strong>Email:</strong> {{ $customer['email'] ?? '' }}</p>

            <h2>Accounts</h2>

            @forelse($customer['accounts'] ?? [] as $account)
                <div class="account-box">
                    <p><strong>Account Number:</strong> {{ $account['accountNumber'] ?? '' }}</p>
                    <p><strong>Account Type:</strong> {{ $account['accountType'] ?? '' }}</p>
                    <p><strong>Balance:</strong> {{ $account['balance'] ?? '' }}</p>
                </div>
            @empty
                <p>No accounts linked yet.</p>
            @endforelse
        @else
            <p>No linked customer profile found for this login.</p>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>

</body>
</html>