<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Statement</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 28px 34px;
        }

        .top {
            width: 100%;
            margin-bottom: 22px;
        }

        .left,
        .right {
            vertical-align: top;
        }

        .left {
            width: 62%;
            display: inline-block;
        }

        .right {
            width: 35%;
            display: inline-block;
            text-align: right;
        }

        .logo-box {
            font-size: 28px;
            font-weight: bold;
            color: #0f4fa8;
            margin-bottom: 16px;
        }

        .statement-title {
            font-size: 26px;
            font-weight: 800;
            margin: 8px 0 14px;
        }

        .meta-row {
            margin-bottom: 6px;
        }

        .meta-label {
            display: inline-block;
            width: 95px;
            color: #444;
        }

        .customer {
            margin-top: 14px;
            line-height: 1.6;
        }

        .section-bar {
            background: #2f75b5;
            color: #fff;
            padding: 7px 10px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 6px 8px;
            border-bottom: 1px solid #d6d6d6;
            font-size: 11px;
        }

        th {
            background: #f3f3f3;
            font-weight: 700;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .note-box {
            border: 1px solid #cfcfcf;
            margin-top: 18px;
            min-height: 120px;
            padding: 10px;
        }

        .footer {
            margin-top: 24px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="top">
        <div class="left">
            <div class="statement-title">Your Account Statement</div>

            <div class="meta-row"><span class="meta-label">Issue Date:</span> {{ \Carbon\Carbon::parse($statement['issue_date'])->format('m/d/Y') }}</div>
            <div class="meta-row"><span class="meta-label">Period:</span> {{ \Carbon\Carbon::parse($statement['period_start'])->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($statement['period_end'])->format('m/d/Y') }}</div>

            <div class="customer">
                <div>{{ $statement['account'] }}</div>
                <div><strong>{{ $statement['customer_name'] }}</strong></div>
                <div>{{ $statement['address_1'] }}</div>
                <div>{{ $statement['address_2'] }}</div>
            </div>
        </div>

        <div class="right">
            <div class="logo-box">Bank of Fiji</div>
            <div style="border:1px solid #cfcfcf; padding:10px; text-align:left;">
                <div><strong>Main Branch</strong></div>
                <div>Suva, Fiji</div>
                <div>Online Banking Services</div>
                <div>support@bankoffiji.com</div>
            </div>
        </div>
    </div>

    <div class="section-bar">Account Activity</div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Payment Type</th>
                <th>Detail</th>
                <th class="num">Paid In</th>
                <th class="num">Paid Out</th>
                <th class="num">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statement['transactions'] as $txn)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($txn['date'])->format('m/d/Y') }}</td>
                    <td>{{ $txn['payment_type'] }}</td>
                    <td>{{ $txn['detail'] }}</td>
                    <td class="num">{{ $txn['paid_in'] }}</td>
                    <td class="num">{{ $txn['paid_out'] }}</td>
                    <td class="num">{{ $txn['balance'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="note-box">
        <strong>Note:</strong>
    </div>

    <div class="footer">
        Bank of Fiji Account Statement
    </div>
</body>
</html>