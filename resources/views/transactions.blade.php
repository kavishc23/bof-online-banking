@extends('layouts.app')

@php
    $pageTitle = 'Transactions - BoF Online Banking';
@endphp

@section('topcard')
    <div class="tx-hero">
        <h2>Transaction History</h2>
        <p>Review your account activity, including transfers, bill payments, and incoming deposits.</p>
    </div>
@endsection

@push('styles')
<style>
    :root {
        --tx-bg: #f4f6fb;
        --tx-surface: #ffffff;
        --tx-surface-2: #f8fafc;
        --tx-surface-3: #eef4ff;
        --tx-border: #d9e1ec;
        --tx-text: #162033;
        --tx-text-soft: #667085;
        --tx-text-muted: #8c97aa;
        --tx-primary: #0b56b3;
        --tx-primary-2: #1d4ed8;
        --tx-row-highlight: #eef5ff;
        --tx-input-bg: #ffffff;
        --tx-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        --tx-soft-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        --tx-processed-bg: #d9f7be;
        --tx-processed-text: #3f8600;
        --tx-pending-bg: #fff4cc;
        --tx-pending-text: #9a6700;
        --tx-failed-bg: #fde2e2;
        --tx-failed-text: #b42318;
        --tx-info-bg: #e8f1ff;
        --tx-info-text: #1d4ed8;
    }

    body.dark-mode {
        --tx-bg: #07162f;
        --tx-surface: #0f1e3b;
        --tx-surface-2: #13274a;
        --tx-surface-3: #173154;
        --tx-border: rgba(255,255,255,0.14);
        --tx-text: #e8eefc;
        --tx-text-soft: #a9b6d3;
        --tx-text-muted: #8fa2c6;
        --tx-primary: #8fb5ff;
        --tx-primary-2: #7eb2ff;
        --tx-row-highlight: rgba(143, 181, 255, 0.12);
        --tx-input-bg: #102241;
        --tx-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
        --tx-soft-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
        --tx-processed-bg: #c9ef9e;
        --tx-processed-text: #2f7a00;
        --tx-pending-bg: #ffe7a3;
        --tx-pending-text: #8b5e00;
        --tx-failed-bg: #f8d7da;
        --tx-failed-text: #9f1239;
        --tx-info-bg: rgba(126, 178, 255, 0.14);
        --tx-info-text: #aecdff;
    }

    .tx-page {
        color: var(--tx-text);
    }

    .tx-card {
        background: var(--tx-surface);
        border: 1px solid var(--tx-border);
        border-radius: 24px;
        box-shadow: var(--tx-shadow);
        overflow: hidden;
        transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    }

    .tx-card-header {
        padding: 24px 24px 16px;
        border-bottom: 1px solid var(--tx-border);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .tx-card-header h3 {
        margin: 0 0 6px;
        color: #c53030;
        font-size: 1.08rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    body.dark-mode .tx-card-header h3 {
        color: #ff9b9b;
    }

    .tx-card-header p {
        margin: 0;
        color: var(--tx-text-soft);
        font-size: 0.95rem;
    }

    .tx-account-switch {
        min-width: 240px;
    }

    .tx-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        padding: 20px 20px 0;
    }

    .tx-summary-box {
        background: var(--tx-surface-2);
        border: 1px solid var(--tx-border);
        border-radius: 16px;
        padding: 16px 16px;
        box-shadow: var(--tx-soft-shadow);
    }

    .tx-summary-box .label {
        margin: 0 0 8px;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--tx-text-soft);
    }

    .tx-summary-box .value {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--tx-text);
    }

    .tx-summary-box .sub {
        margin-top: 6px;
        color: var(--tx-text-muted);
        font-size: 0.82rem;
    }

    .tx-filters {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1fr auto;
        gap: 12px;
        padding: 18px 20px;
        align-items: center;
    }

    .tx-input,
    .tx-select {
        width: 100%;
        min-height: 46px;
        padding: 10px 14px;
        border: 1px solid var(--tx-border);
        border-radius: 12px;
        background: var(--tx-input-bg);
        color: var(--tx-text);
        font-size: 0.95rem;
        outline: none;
        transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    }

    .tx-input::placeholder {
        color: var(--tx-text-soft);
    }

    .tx-input:focus,
    .tx-select:focus {
        border-color: var(--tx-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
    }

    .tx-btn {
        min-height: 46px;
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .tx-btn-primary {
        background: var(--tx-primary);
        color: #fff;
    }

    .tx-btn-primary:hover {
        filter: brightness(1.05);
    }

    .tx-results {
        padding: 0 20px 14px;
        color: var(--tx-text-soft);
        font-size: 0.92rem;
    }

    .tx-table-wrap {
        padding: 0 0 10px;
    }

    .tx-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .tx-table thead th {
        text-align: left;
        padding: 15px 16px;
        font-size: 0.83rem;
        font-weight: 800;
        color: var(--tx-text-soft);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-top: 1px solid var(--tx-border);
        border-bottom: 1px solid var(--tx-border);
        background: var(--tx-surface);
        white-space: nowrap;
    }

    .tx-table tbody td {
        padding: 16px 16px;
        border-bottom: 1px solid var(--tx-border);
        color: var(--tx-text);
        vertical-align: middle;
        background: var(--tx-surface);
    }

    .tx-table tbody tr.highlight td {
        background: var(--tx-row-highlight);
    }

    .tx-table tbody tr:hover td {
        background: var(--tx-surface-2);
    }

    .tx-table tbody tr.highlight:hover td {
        background: var(--tx-row-highlight);
    }

    .tx-type-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tx-type-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(11, 86, 179, 0.08);
        color: var(--tx-primary);
        border: 1px solid rgba(11, 86, 179, 0.12);
    }

    body.dark-mode .tx-type-icon {
        background: rgba(143, 181, 255, 0.10);
        border-color: rgba(143, 181, 255, 0.20);
    }

    .tx-type-meta {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .tx-type-name {
        font-weight: 700;
        color: var(--tx-text);
    }

    .tx-type-sub {
        font-size: 0.82rem;
        color: var(--tx-text-soft);
    }

    .tx-date {
        font-weight: 600;
        color: var(--tx-primary);
        white-space: nowrap;
    }

    .tx-reference {
        font-weight: 600;
        color: var(--tx-text);
        font-family: monospace;
        font-size: 0.92rem;
    }

    .tx-ref {
        background: rgba(11, 86, 179, 0.08);
        padding: 5px 9px;
        border-radius: 8px;
        display: inline-block;
    }

    body.dark-mode .tx-ref {
        background: rgba(143, 181, 255, 0.12);
    }

    .tx-beneficiary {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .tx-beneficiary-name {
        font-weight: 700;
        color: var(--tx-text);
    }

    .tx-beneficiary-sub {
        font-size: 0.82rem;
        color: var(--tx-text-soft);
    }

    .tx-amount {
        font-weight: 800;
        white-space: nowrap;
        color: var(--tx-text);
    }

    .tx-reason {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .tx-reason-main {
        color: var(--tx-text);
        font-weight: 600;
    }

    .tx-reason-sub {
        font-size: 0.82rem;
        color: var(--tx-text-soft);
    }

    .tx-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .tx-status.processed,
    .tx-status.completed {
        background: var(--tx-processed-bg);
        color: var(--tx-processed-text);
    }

    .tx-status.pending {
        background: var(--tx-pending-bg);
        color: var(--tx-pending-text);
    }

    .tx-status.failed {
        background: var(--tx-failed-bg);
        color: var(--tx-failed-text);
    }

    .tx-empty {
        padding: 28px 20px 32px;
        color: var(--tx-text-soft);
    }

    .tx-empty-box {
        background: var(--tx-surface-2);
        border: 1px dashed var(--tx-border);
        border-radius: 16px;
        padding: 22px;
        text-align: center;
    }

    .tx-hidden {
        display: none !important;
    }

    @media (max-width: 1100px) {
        .tx-summary {
            grid-template-columns: 1fr 1fr;
        }

        .tx-filters {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 700px) {
        .tx-summary {
            grid-template-columns: 1fr;
        }

        .tx-filters {
            grid-template-columns: 1fr;
        }

        .tx-table-wrap {
            overflow-x: auto;
        }

        .tx-table {
            min-width: 1100px;
        }
    }
</style>
@endpush

@section('content')
@php
    $transactionCount = count($transactions ?? []);
    $totalIn = 0;
    $totalOut = 0;
    $selectedAccountNumber = null;

    foreach (($accounts ?? []) as $acc) {
        if ((string)($selectedAccountId ?? '') === (string)($acc['id'] ?? '')) {
            $selectedAccountNumber = $acc['accountNumber'] ?? null;
            break;
        }
    }

    foreach (($transactions ?? []) as $transaction) {
        $transactionType = strtolower($transaction['transactionType'] ?? '');
        $transferType = strtolower($transaction['transferType'] ?? '');
        $amount = (float) ($transaction['amount'] ?? 0);

        if ($transactionType === 'deposit' || $transferType === 'deposit') {
            $totalIn += $amount;
        } else {
            $totalOut += $amount;
        }
    }
@endphp

<div class="tx-page">
    <div class="tx-card">
        <div class="tx-card-header">
            <div>
                <h3>Transaction Register</h3>
                <p>View activity by account, search references, and monitor processed and pending items.</p>
            </div>

            <div class="tx-account-switch">
                <select class="tx-select" onchange="window.location=this.value;">
                    <option value="{{ route('transactions') }}" {{ empty($selectedAccountId) ? 'selected' : '' }}>
                        All Linked Accounts
                    </option>
                    @foreach(($accounts ?? []) as $account)
                        <option
                            value="{{ route('transactions', ['account_id' => $account['id']]) }}"
                            {{ (string)($selectedAccountId ?? '') === (string)($account['id'] ?? '') ? 'selected' : '' }}
                        >
                            {{ $account['accountNumber'] ?? 'N/A' }} - {{ $account['accountType'] ?? 'Account' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="tx-summary">
            <div class="tx-summary-box">
                <p class="label">Transactions Shown</p>
                <p class="value">{{ $transactionCount }}</p>
                <div class="sub">
                    {{ $selectedAccountNumber ? 'Account ' . $selectedAccountNumber : 'All linked customer accounts' }}
                </div>
            </div>

            <div class="tx-summary-box">
                <p class="label">Total Incoming</p>
                <p class="value">{{ number_format($totalIn, 2) }} FJD</p>
                <div class="sub">Credits and received deposits</div>
            </div>

            <div class="tx-summary-box">
                <p class="label">Total Outgoing</p>
                <p class="value">{{ number_format($totalOut, 2) }} FJD</p>
                <div class="sub">Transfers and bill payments</div>
            </div>

            <div class="tx-summary-box">
                <p class="label">Current View</p>
                <p class="value" style="font-size:1.05rem;">
                    {{ $selectedAccountNumber ? 'Account ' . $selectedAccountNumber : 'All Accounts' }}
                </p>
                <div class="sub">Switch account from the selector above</div>
            </div>
        </div>

        <div class="tx-filters">
            <input
                type="text"
                id="txSearch"
                class="tx-input"
                placeholder="Search beneficiary, reason, account, reference"
            >

            <select id="txStatusFilter" class="tx-select">
                <option value="">All Statuses</option>
                <option value="processed">Processed</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>

            <select id="txAccountFilter" class="tx-select">
                <option value="">All Accounts In Current View</option>
                @foreach(($accounts ?? []) as $account)
                    <option value="{{ strtolower($account['accountNumber'] ?? '') }}">
                        {{ $account['accountNumber'] ?? 'N/A' }}
                    </option>
                @endforeach
            </select>

            <input type="date" id="txDateFilter" class="tx-input">

            <button type="button" id="txClearFilters" class="tx-btn tx-btn-primary">
                Clear Filters
            </button>
        </div>

        <div id="txResults" class="tx-results">
            Showing {{ $transactionCount }} of {{ $transactionCount }} transactions
        </div>

        <div class="tx-table-wrap">
            @if($transactionCount > 0)
                <table class="tx-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Beneficiary / From</th>
                            <th>Amount</th>
                            <th>Details</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTableBody">
                        @foreach($transactions as $index => $transaction)
                            @php
                                $transactionType = strtolower($transaction['transactionType'] ?? '-');
                                $transferType = strtolower($transaction['transferType'] ?? '-');
                                $statusRaw = strtolower($transaction['transactionStatus'] ?? 'completed');
                                $amount = (float) ($transaction['amount'] ?? 0);

                                $reference = $transaction['referenceNumber'] ?? '-';

                                $accountNumber = $transaction['account']['accountNumber']
                                    ?? $transaction['sourceAccount']['accountNumber']
                                    ?? $transaction['destinationAccount']['accountNumber']
                                    ?? '-';

                                $beneficiary = $transaction['beneficiaryName']
                                    ?? $transaction['billerName']
                                    ?? ($transactionType === 'deposit' ? 'Incoming Funds' : '-');

                                $reason = $transaction['description'] ?? ($transaction['remarks'] ?? '-');

                                $dateObject = !empty($transaction['transactionDate'])
                                    ? \Carbon\Carbon::parse($transaction['transactionDate'])
                                    : null;

                                $displayDate = $dateObject ? $dateObject->format('d/m/Y') : '-';
                                $dateValue = $dateObject ? $dateObject->format('Y-m-d') : '';

                                $typeLabel = match($transferType) {
                                    'billpayment' => 'Bill Payment',
                                    'localbank' => 'Local Bank Transfer',
                                    'deposit' => 'Deposit',
                                    'internal' => 'Internal Transfer',
                                    default => ucfirst($transferType ?: $transactionType ?: 'Transaction'),
                                };

                                $typeSub = 'Acct: ' . $accountNumber;

                                $reasonSub = $transaction['destinationInstitution']
                                    ?? ($transaction['destinationAccountNumber'] ?? '');

                                $searchBlob = strtolower(trim(
                                    ($beneficiary ?? '') . ' ' .
                                    ($reason ?? '') . ' ' .
                                    ($accountNumber ?? '') . ' ' .
                                    ($reference ?? '') . ' ' .
                                    ($transferType ?? '') . ' ' .
                                    ($reasonSub ?? '')
                                ));
                            @endphp

                            <tr
                                class="{{ $index === 0 ? 'highlight' : '' }}"
                                data-status="{{ $statusRaw }}"
                                data-account="{{ strtolower($accountNumber) }}"
                                data-date="{{ $dateValue }}"
                                data-search="{{ $searchBlob }}"
                            >
                                <td>
                                    <div class="tx-type-wrap">
                                        <span class="tx-type-icon" title="{{ $typeLabel }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                @if($transferType === 'billpayment')
                                                    <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"></path>
                                                    <path d="M9 8h6M9 12h6"></path>
                                                @elseif($transferType === 'localbank')
                                                    <path d="M3 10l9-6 9 6"></path>
                                                    <path d="M5 10v8M9 10v8M15 10v8M19 10v8"></path>
                                                    <path d="M3 18h18"></path>
                                                @elseif($transferType === 'deposit')
                                                    <path d="M12 4v16"></path>
                                                    <path d="M7 9l5-5 5 5"></path>
                                                @else
                                                    <path d="M3 11l9-8 9 8"></path>
                                                    <path d="M5 10v10h14V10"></path>
                                                    <path d="M9 20v-6h6v6"></path>
                                                @endif
                                            </svg>
                                        </span>

                                        <div class="tx-type-meta">
                                            <span class="tx-type-name">{{ $typeLabel }}</span>
                                            <span class="tx-type-sub">{{ $typeSub }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="tx-date">{{ $displayDate }}</td>

                                <td class="tx-reference">
                                    <span class="tx-ref">{{ $reference }}</span>
                                </td>

                                <td>
                                    <div class="tx-beneficiary">
                                        <span class="tx-beneficiary-name">{{ $beneficiary }}</span>
                                        <span class="tx-beneficiary-sub">
                                            {{ $transaction['destinationAccountNumber'] ?? $transaction['destinationInstitution'] ?? 'Recorded transaction' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="tx-amount">{{ number_format($amount, 2) }} FJD</td>

                                <td>
                                    <div class="tx-reason">
                                        <span class="tx-reason-main">{{ $reason }}</span>
                                        <span class="tx-reason-sub">
                                            {{ !empty($reasonSub) ? $reasonSub : 'No additional details' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="tx-status {{ $statusRaw }}">
                                        {{ strtoupper($statusRaw === 'completed' ? 'processed' : $statusRaw) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="txNoMatch" class="tx-empty tx-hidden">
                    <div class="tx-empty-box">
                        No transactions match the selected filters.
                    </div>
                </div>
            @else
                <div class="tx-empty">
                    <div class="tx-empty-box">
                        No transactions found for the selected account view.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const txSearch = document.getElementById('txSearch');
        const txStatusFilter = document.getElementById('txStatusFilter');
        const txAccountFilter = document.getElementById('txAccountFilter');
        const txDateFilter = document.getElementById('txDateFilter');
        const txClearFilters = document.getElementById('txClearFilters');
        const txNoMatch = document.getElementById('txNoMatch');
        const txResults = document.getElementById('txResults');
        const txRows = Array.from(document.querySelectorAll('#transactionTableBody tr'));

        function statusMatches(rowStatus, selectedStatus) {
            if (!selectedStatus) return true;

            if (selectedStatus === 'processed') {
                return rowStatus === 'processed' || rowStatus === 'completed';
            }

            if (selectedStatus === 'completed') {
                return rowStatus === 'completed' || rowStatus === 'processed';
            }

            return rowStatus === selectedStatus;
        }

        function applyTransactionFilters() {
            const searchValue = (txSearch?.value || '').toLowerCase().trim();
            const statusValue = (txStatusFilter?.value || '').toLowerCase().trim();
            const accountValue = (txAccountFilter?.value || '').toLowerCase().trim();
            const dateValue = (txDateFilter?.value || '').trim();

            let visibleCount = 0;

            txRows.forEach((row) => {
                const rowSearch = (row.dataset.search || '').toLowerCase();
                const rowStatus = (row.dataset.status || '').toLowerCase();
                const rowAccount = (row.dataset.account || '').toLowerCase();
                const rowDate = row.dataset.date || '';

                const matchesSearch = !searchValue || rowSearch.includes(searchValue);
                const matchesStatus = statusMatches(rowStatus, statusValue);
                const matchesAccount = !accountValue || rowAccount === accountValue;
                const matchesDate = !dateValue || rowDate === dateValue;

                const shouldShow = matchesSearch && matchesStatus && matchesAccount && matchesDate;

                row.classList.toggle('tx-hidden', !shouldShow);

                if (shouldShow) visibleCount++;
            });

            if (txNoMatch) {
                txNoMatch.classList.toggle('tx-hidden', visibleCount !== 0);
            }

            if (txResults) {
                txResults.textContent = `Showing ${visibleCount} of ${txRows.length} transactions`;
            }
        }

        if (txSearch) txSearch.addEventListener('input', applyTransactionFilters);
        if (txStatusFilter) txStatusFilter.addEventListener('change', applyTransactionFilters);
        if (txAccountFilter) txAccountFilter.addEventListener('change', applyTransactionFilters);
        if (txDateFilter) txDateFilter.addEventListener('change', applyTransactionFilters);

        if (txClearFilters) {
            txClearFilters.addEventListener('click', function () {
                if (txSearch) txSearch.value = '';
                if (txStatusFilter) txStatusFilter.value = '';
                if (txAccountFilter) txAccountFilter.value = '';
                if (txDateFilter) txDateFilter.value = '';
                applyTransactionFilters();
            });
        }

        applyTransactionFilters();
    })();
</script>
@endpush