<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Net Income Report</title>
    <style>
        @page { margin: 32px 34px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 12px;
            line-height: 1.45;
        }
        .header {
            border-bottom: 3px solid #163d7a;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .brand {
            font-size: 25px;
            font-weight: bold;
            color: #163d7a;
        }
        .subtitle {
            color: #475569;
            margin-top: 4px;
        }
        h1 {
            font-size: 22px;
            margin: 0 0 8px;
            color: #163d7a;
        }
        h2 {
            font-size: 15px;
            margin: 22px 0 10px;
            color: #163d7a;
            border-bottom: 1px solid #dbeafe;
            padding-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th {
            background: #e8f1ff;
            color: #163d7a;
            text-align: left;
            font-weight: bold;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            vertical-align: top;
        }
        .summary-grid {
            width: 100%;
            margin: 14px 0;
        }
        .summary-grid td {
            width: 25%;
            background: #f8fafc;
        }
        .metric-label {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .metric-value {
            display: block;
            margin-top: 4px;
            font-size: 16px;
            color: #163d7a;
            font-weight: bold;
        }
        .total-row td {
            background: #eef6ff;
            font-weight: bold;
        }
        .positive {
            color: #166534;
            font-weight: bold;
        }
        .negative {
            color: #991b1b;
            font-weight: bold;
        }
        .notes {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 28px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            color: #64748b;
            font-size: 10px;
        }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Bank of Fiji</div>
        <div class="subtitle">Management Reporting | Generated {{ $report['generated_at'] }}</div>
    </div>

    <h1>Net Income Report</h1>
    <table>
        <tr>
            <td><strong>Date Range</strong></td>
            <td>{{ $report['report_start_date'] }} to {{ $report['report_end_date'] }}</td>
            <td><strong>Interest Days</strong></td>
            <td>{{ $report['interest_days'] }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td class="{{ $report['net_income'] >= 0 ? 'positive' : 'negative' }}">{{ $report['net_income_status'] }}</td>
            <td><strong>Highest Fee Category</strong></td>
            <td>{{ $report['highest_fee_category'] }}</td>
        </tr>
    </table>

    @if(!empty($report['notes']))
        <div class="notes">
            <strong>Management Notes:</strong><br>
            {{ $report['notes'] }}
        </div>
    @endif

    <h2>Executive Summary</h2>
    <table class="summary-grid">
        <tr>
            <td>
                <span class="metric-label">Fees Collected</span>
                <span class="metric-value">${{ number_format((float) $report['total_fees_collected'], 2) }}</span>
            </td>
            <td>
                <span class="metric-label">Interest Paid</span>
                <span class="metric-value">${{ number_format((float) $report['total_interest_paid'], 2) }}</span>
            </td>
            <td>
                <span class="metric-label">Net Income</span>
                <span class="metric-value">${{ number_format((float) $report['net_income'], 2) }}</span>
            </td>
            <td>
                <span class="metric-label">Fee Transactions</span>
                <span class="metric-value">{{ $report['number_of_fee_transactions'] }}</span>
            </td>
        </tr>
    </table>

    <h2>Fee Income Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="center">Transactions</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monthly Account Fees</td>
                <td class="center">{{ count($report['fee_rows']['monthly_account_fee']) }}</td>
                <td class="right">${{ number_format((float) $report['total_monthly_account_fees'], 2) }}</td>
            </tr>
            <tr>
                <td>Savings Withdrawal Fees</td>
                <td class="center">{{ count($report['fee_rows']['withdrawal_fee']) }}</td>
                <td class="right">${{ number_format((float) $report['total_withdrawal_fees'], 2) }}</td>
            </tr>
            <tr>
                <td>Other Fees</td>
                <td class="center">{{ count($report['fee_rows']['other_fee']) }}</td>
                <td class="right">${{ number_format((float) $report['total_other_fees'], 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Fees Collected</td>
                <td class="center">{{ $report['number_of_fee_transactions'] }}</td>
                <td class="right">${{ number_format((float) $report['total_fees_collected'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Net Income Calculation</h2>
    <table>
        <tr>
            <td>Total Fees Collected</td>
            <td class="right">${{ number_format((float) $report['total_fees_collected'], 2) }}</td>
        </tr>
        <tr>
            <td>Less: Estimated Interest Paid</td>
            <td class="right">${{ number_format((float) $report['total_interest_paid'], 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Net Income</td>
            <td class="right {{ $report['net_income'] >= 0 ? 'positive' : 'negative' }}">${{ number_format((float) $report['net_income'], 2) }}</td>
        </tr>
    </table>

    @if($report['include_account_interest_breakdown'])
        <h2>Interest Paid Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Account Number</th>
                    <th>Type</th>
                    <th class="right">Balance</th>
                    <th class="right">Interest Rate</th>
                    <th class="right">Estimated Interest</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['interest_rows'] as $row)
                    <tr>
                        <td>{{ $row['accountNumber'] }}</td>
                        <td>{{ $row['accountType'] }}</td>
                        <td class="right">${{ number_format((float) $row['balance'], 2) }}</td>
                        <td class="right">{{ number_format((float) $row['interestRate'], 2) }}%</td>
                        <td class="right">${{ number_format((float) $row['estimatedInterestPaid'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No accounts found.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="4">Total Estimated Interest Paid</td>
                    <td class="right">${{ number_format((float) $report['total_interest_paid'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($report['include_transaction_details'])
        <h2>Detailed Fee Transactions</h2>
        @php
            $hasFeeTransactions = collect($report['fee_rows'])->flatten(1)->isNotEmpty();
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $categoryLabels = [
                        'monthly_account_fee' => 'Monthly Account Fee',
                        'withdrawal_fee' => 'Savings Withdrawal Fee',
                        'other_fee' => 'Other Fee',
                    ];
                @endphp
                @if(! $hasFeeTransactions)
                    <tr><td colspan="5">No fee transactions found for this report period.</td></tr>
                @endif
                @foreach($report['fee_rows'] as $category => $transactions)
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction['referenceNumber'] ?? '-' }}</td>
                            <td>{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('Y-m-d') : '-' }}</td>
                            <td>{{ $categoryLabels[$category] ?? 'Fee' }}</td>
                            <td>{{ $transaction['description'] ?? '-' }}</td>
                            <td class="right">${{ number_format((float) ($transaction['amount'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Bank of Fiji CS415 Assignment 3 management report. Figures are generated from Strapi account and transaction data for the selected reporting period.
    </div>
</body>
</html>
