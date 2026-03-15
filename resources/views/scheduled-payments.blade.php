@extends('layouts.app')

@php
    $pageTitle = 'Scheduled Payments - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Scheduled Payments</h2>
    <p>Review your upcoming scheduled transfers and bill payments in one place.</p>
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

    body.dark-mode .stat-card {
        background: rgba(17,24,39,0.92);
    }

    .stat-card h3 {
        margin: 0 0 10px;
        color: #6b7280;
        font-size: 15px;
        font-weight: 600;
    }

    body.dark-mode .stat-card h3 {
        color: #cbd5e1;
    }

    .stat-card .value {
        font-size: 30px;
        font-weight: bold;
        color: #163d7a;
    }

    body.dark-mode .stat-card .value {
        color: #bfdbfe;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .section {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }

    body.dark-mode .section {
        background: rgba(17,24,39,0.92);
    }

    .section h3 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .section h3 {
        color: #bfdbfe;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: 0;
        margin-bottom: 18px;
    }

    body.dark-mode .section-note {
        color: #cbd5e1;
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

    body.dark-mode table {
        background: rgba(17,24,39,0.92);
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
        background: #172338;
        color: #bfdbfe;
    }

    tr:hover {
        background: #f9fbff;
    }

    body.dark-mode tr:hover {
        background: rgba(30, 41, 59, 0.65);
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
        white-space: nowrap;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-processed {
        background: #dcfce7;
        color: #166534;
    }

    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .type-badge {
        background: #dbeafe;
        color: #1d4ed8;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
    }

    body.dark-mode .type-badge {
        background: rgba(30, 58, 138, 0.28);
        color: #bfdbfe;
    }

    .amount {
        font-weight: bold;
        color: #b91c1c;
    }

    .amount-neutral {
        font-weight: bold;
        color: #163d7a;
    }

    body.dark-mode .amount-neutral {
        color: #bfdbfe;
    }

    .meta-line {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
        line-height: 1.45;
    }

    body.dark-mode .meta-line {
        color: #cbd5e1;
    }

    .empty-state {
        color: #6b7280;
        font-size: 15px;
        padding: 8px 0;
    }

    body.dark-mode .empty-state {
        color: #cbd5e1;
    }

    .summary-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 24px;
    }

    body.dark-mode .summary-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .summary-box h4 {
        margin: 0 0 10px;
        color: #163d7a;
        font-size: 16px;
    }

    body.dark-mode .summary-box h4 {
        color: #bfdbfe;
    }

    .summary-box p {
        margin: 0;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.6;
    }

    body.dark-mode .summary-box p {
        color: #cbd5e1;
    }

    @media (max-width: 1100px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    @php
        $scheduledTransfers = $scheduledTransfers ?? [];
        $scheduledBillPayments = $scheduledBillPayments ?? [];

        $transferCount = count($scheduledTransfers);
        $billCount = count($scheduledBillPayments);
        $totalScheduled = $transferCount + $billCount;

        $scheduledTransferAmount = 0;
        $scheduledBillAmount = 0;

        foreach ($scheduledTransfers as $transfer) {
            $scheduledTransferAmount += (float)($transfer['amount'] ?? 0);
        }

        foreach ($scheduledBillPayments as $bill) {
            $scheduledBillAmount += (float)($bill['amount'] ?? 0);
        }
    @endphp

    <div class="stats-row">
        <div class="stat-card">
            <h3>Total Scheduled Items</h3>
            <div class="value">{{ $totalScheduled }}</div>
        </div>

        <div class="stat-card">
            <h3>Scheduled Transfers</h3>
            <div class="value">{{ $transferCount }}</div>
        </div>

        <div class="stat-card">
            <h3>Scheduled Bills</h3>
            <div class="value">{{ $billCount }}</div>
        </div>

        <div class="stat-card">
            <h3>Total Future Amount</h3>
            <div class="value">${{ number_format($scheduledTransferAmount + $scheduledBillAmount, 2) }}</div>
        </div>
    </div>

    <div class="summary-box">
        <h4>Overview</h4>
        <p>
            This page shows all future-dated transfer instructions and bill payments saved in your account.
            Scheduled items are not deducted immediately. They remain pending until their scheduled execution date.
        </p>
    </div>

    <div class="content-grid">
        <section class="section">
            <h3>Scheduled Transfers</h3>
            <p class="section-note">Includes internal BoF transfers and transfers to other local banks or wallets.</p>

            @if(count($scheduledTransfers) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Destination</th>
                                <th>Amount</th>
                                <th>Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduledTransfers as $transfer)
                                @php
                                    $status = strtolower($transfer['scheduleStatus'] ?? 'pending');
                                    $transferMode = $transfer['transferMode'] ?? 'Transfer';
                                    $scheduledDate = $transfer['scheduledDate'] ?? null;
                                    $frequency = $transfer['frequency'] ?? 'Once';
                                    $destinationInstitution = $transfer['destinationInstitution'] ?? 'BoF';
                                    $destinationAccountNumber = $transfer['destinationAccountNumber'] ?? '-';
                                    $beneficiaryName = $transfer['beneficiaryName'] ?? '';
                                @endphp
                                <tr>
                                    <td>
                                        {{ $transfer['referenceNumber'] ?? '-' }}
                                        <div class="meta-line">
                                            Source:
                                            {{ $transfer['sourceAccount']['accountNumber'] ?? ($transfer['sourceAccount']['id'] ?? '-') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="type-badge">{{ $transferMode }}</span>
                                        <div class="meta-line">
                                            {{ $transfer['description'] ?? 'Scheduled transfer' }}
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $destinationInstitution }}</strong>
                                        <div class="meta-line">Acct: {{ $destinationAccountNumber }}</div>
                                        @if(!empty($beneficiaryName))
                                            <div class="meta-line">Beneficiary: {{ $beneficiaryName }}</div>
                                        @endif
                                    </td>
                                    <td class="amount">
                                        ${{ number_format((float)($transfer['amount'] ?? 0), 2) }}
                                    </td>
                                    <td>
                                        @if(!empty($scheduledDate))
                                            {{ \Carbon\Carbon::parse($scheduledDate)->format('d M Y, h:i A') }}
                                        @else
                                            -
                                        @endif
                                        <div class="meta-line">Frequency: {{ $frequency }}</div>
                                    </td>
                                    <td>
                                        @if($status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($status === 'processed')
                                            <span class="badge badge-processed">Processed</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge badge-cancelled">Cancelled</span>
                                        @else
                                            <span class="badge badge-pending">{{ $transfer['scheduleStatus'] ?? 'Pending' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty-state">No scheduled transfers found.</p>
            @endif
        </section>

        <section class="section">
            <h3>Scheduled Bill Payments</h3>
            <p class="section-note">Future utility, telecom, and service bill payments saved for later processing.</p>

            @if(count($scheduledBillPayments) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Biller</th>
                                <th>Amount</th>
                                <th>Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduledBillPayments as $bill)
                                @php
                                    $status = strtolower($bill['scheduleStatus'] ?? 'pending');
                                    $scheduledDate = $bill['scheduledDate'] ?? null;
                                    $frequency = $bill['frequency'] ?? 'Once';
                                @endphp
                                <tr>
                                    <td>
                                        {{ $bill['referenceNumber'] ?? '-' }}
                                        <div class="meta-line">
                                            Source:
                                            {{ $bill['sourceAccount']['accountNumber'] ?? ($bill['sourceAccount']['id'] ?? '-') }}
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $bill['billerName'] ?? '-' }}</strong>
                                        @if(!empty($bill['billReference']))
                                            <div class="meta-line">Reference: {{ $bill['billReference'] }}</div>
                                        @endif
                                        @if(!empty($bill['notes']))
                                            <div class="meta-line">{{ $bill['notes'] }}</div>
                                        @endif
                                    </td>
                                    <td class="amount-neutral">
                                        ${{ number_format((float)($bill['amount'] ?? 0), 2) }}
                                    </td>
                                    <td>
                                        @if(!empty($scheduledDate))
                                            {{ \Carbon\Carbon::parse($scheduledDate)->format('d M Y, h:i A') }}
                                        @else
                                            -
                                        @endif
                                        <div class="meta-line">Frequency: {{ $frequency }}</div>
                                    </td>
                                    <td>
                                        @if($status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($status === 'processed')
                                            <span class="badge badge-processed">Processed</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge badge-cancelled">Cancelled</span>
                                        @else
                                            <span class="badge badge-pending">{{ $bill['scheduleStatus'] ?? 'Pending' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty-state">No scheduled bill payments found.</p>
            @endif
        </section>
    </div>
@endsection