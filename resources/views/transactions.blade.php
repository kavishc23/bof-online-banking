@extends('layouts.app')

@php
    $pageTitle = 'Transactions - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Transaction History</h2>
    <p>Review all account activity, including internal transfers, local bank transfers, bill payments, deposits, and timestamps.</p>
@endsection

@push('styles')
<style>
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

    body.dark-mode .stat-card,
    body.dark-mode .section {
        background: rgba(17, 24, 39, 0.9);
        border: 1px solid rgba(51, 65, 85, 0.8);
    }

    .stat-card h3 {
        margin: 0 0 10px;
        color: #6b7280;
        font-size: 15px;
        font-weight: 600;
    }

    body.dark-mode .stat-card h3,
    body.dark-mode .section-note,
    body.dark-mode .filter-note,
    body.dark-mode .empty-state,
    body.dark-mode .tx-subtext {
        color: #cbd5e1;
    }

    .stat-card .value {
        font-size: 30px;
        font-weight: bold;
        color: #163d7a;
    }

    body.dark-mode .stat-card .value,
    body.dark-mode .section h3,
    body.dark-mode .tx-main {
        color: #bfdbfe;
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

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        min-width: 1200px;
    }

    body.dark-mode table {
        background: #111827;
    }

    th, td {
        padding: 14px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        font-size: 14px;
        vertical-align: top;
    }

    body.dark-mode th,
    body.dark-mode td {
        border-bottom-color: #334155;
    }

    th {
        background: #eaf2ff;
        color: #163d7a;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    body.dark-mode th {
        background: #0f172a;
        color: #bfdbfe;
    }

    tr:hover {
        background: #f9fbff;
    }

    body.dark-mode tr:hover {
        background: rgba(30, 41, 59, 0.55);
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
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

    .filter-note {
        font-size: 13px;
        color: #6b7280;
        margin-top: 10px;
    }

    .tx-main {
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .tx-subtext {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.4;
    }

    .muted {
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
    @php
        $transactionCount = count($transactions ?? []);
        $totalIn = 0;
        $totalOut = 0;
        $internalCount = 0;
        $localCount = 0;
        $billCount = 0;

        foreach (($transactions ?? []) as $transaction) {
            $transactionType = strtolower($transaction['transactionType'] ?? '');
            $transferType = strtolower($transaction['transferType'] ?? '');
            $amount = (float)($transaction['amount'] ?? 0);

            if ($transactionType === 'deposit' || $transferType === 'deposit') {
                $totalIn += $amount;
            } else {
                $totalOut += $amount;
            }

            if ($transferType === 'internal') {
                $internalCount++;
            } elseif ($transferType === 'localbank') {
                $localCount++;
            } elseif ($transferType === 'billpayment') {
                $billCount++;
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

        <div class="stat-card">
            <h3>Internal Transfers</h3>
            <div class="value">{{ $internalCount }}</div>
        </div>

        <div class="stat-card">
            <h3>Local Bank Transfers</h3>
            <div class="value">{{ $localCount }}</div>
        </div>

        <div class="stat-card">
            <h3>Bill Payments</h3>
            <div class="value">{{ $billCount }}</div>
        </div>
    </div>

    <section class="section">
        <h3>All Transactions</h3>
        <p class="section-note">This page shows detailed activity history across your linked accounts, including transfer mode, destination details, and exact timestamps.</p>

        @if(count($transactions) > 0)
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Transfer Mode</th>
                            <th>Institution / Biller</th>
                            <th>Beneficiary</th>
                            <th>Destination</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $transactionType = $transaction['transactionType'] ?? '-';
                                $transferType = $transaction['transferType'] ?? '-';
                                $status = strtolower($transaction['transactionStatus'] ?? 'completed');
                                $amount = (float)($transaction['amount'] ?? 0);

                                $isIncoming = strtolower($transactionType) === 'deposit' || strtolower($transferType) === 'deposit';
                                $amountClass = $isIncoming ? 'amount-positive' : 'amount-negative';

                                $institution = $transaction['destinationInstitution'] ?? 'BoF';
                                $beneficiary = $transaction['beneficiaryName'] ?? '-';
                                $destinationAccount = $transaction['destinationAccountNumber'] ?? '-';

                                $transferTypeLower = strtolower($transferType);
                            @endphp

                            <tr>
                                <td>
                                    <div class="tx-main">{{ $transaction['referenceNumber'] ?? '-' }}</div>
                                    <div class="tx-subtext">Ref No.</div>
                                </td>

                                <td>
                                    <div class="tx-main">{{ $transaction['account']['accountNumber'] ?? '-' }}</div>
                                    <div class="tx-subtext">{{ $transaction['account']['accountType'] ?? 'Linked Account' }}</div>
                                </td>

                                <td>
                                    <div class="tx-main">{{ $transactionType }}</div>
                                    <div class="tx-subtext">Base type</div>
                                </td>

                                <td>
                                    @if($transferTypeLower === 'internal')
                                        <span class="badge badge-internal">Internal</span>
                                    @elseif($transferTypeLower === 'localbank')
                                        <span class="badge badge-local">Local Bank</span>
                                    @elseif($transferTypeLower === 'billpayment')
                                        <span class="badge badge-bill">Bill Payment</span>
                                    @elseif($transferTypeLower === 'deposit')
                                        <span class="badge badge-deposit">Deposit</span>
                                    @else
                                        <span class="badge badge-completed">{{ $transferType }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="tx-main">{{ $institution ?: '-' }}</div>
                                    <div class="tx-subtext">Institution</div>
                                </td>

                                <td>
                                    <div class="tx-main">{{ $beneficiary ?: '-' }}</div>
                                    <div class="tx-subtext">Beneficiary</div>
                                </td>

                                <td>
                                    <div class="tx-main">{{ $destinationAccount ?: '-' }}</div>
                                    <div class="tx-subtext">Destination account / reference</div>
                                </td>

                                <td class="{{ $amountClass }}">
                                    {{ $isIncoming ? '+' : '-' }}${{ number_format($amount, 2) }}
                                </td>

                                <td>
                                    @if($status === 'completed')
                                        <span class="badge badge-completed">Completed</span>
                                    @elseif($status === 'pending')
                                        <span class="badge badge-pending">Pending</span>
                                    @elseif($status === 'failed')
                                        <span class="badge badge-failed">Failed</span>
                                    @else
                                        <span class="badge badge-completed">{{ $transaction['transactionStatus'] ?? 'Completed' }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if(!empty($transaction['transactionDate']))
                                        <div class="tx-main">{{ \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') }}</div>
                                        <div class="tx-subtext">{{ \Carbon\Carbon::parse($transaction['transactionDate'])->format('h:i:s A') }}</div>
                                    @else
                                        <span class="muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="tx-main">{{ $transaction['description'] ?? '-' }}</div>
                                    <div class="tx-subtext">{{ $transaction['remarks'] ?? 'No additional remarks' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="filter-note">Incoming transactions are shown with a + amount in green, while outgoing transactions are shown with a - amount in red.</p>
        @else
            <p class="empty-state">No transactions found for your account.</p>
        @endif
    </section>
@endsection