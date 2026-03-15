@extends('layouts.app')

@php
    $pageTitle = 'Transactions - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Transaction History</h2>
    <p>Review all account activity, including internal transfers, local bank transfers, bill payments, deposits, and exact timestamps.</p>
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
    body.dark-mode .tx-subtext,
    body.dark-mode .filter-label,
    body.dark-mode .results-note {
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

    .filters-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto auto;
        gap: 14px;
        margin-bottom: 18px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-label {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        background: #fff;
        color: #111827;
    }

    body.dark-mode .filter-input,
    body.dark-mode .filter-select {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .clear-btn,
    .export-btn {
        border: none;
        border-radius: 12px;
        padding: 11px 16px;
        font-weight: 700;
        cursor: pointer;
        height: 44px;
        white-space: nowrap;
    }

    .clear-btn {
        background: #e2e8f0;
        color: #0f172a;
    }

    .export-btn {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: white;
    }

    body.dark-mode .clear-btn {
        background: #334155;
        color: #f8fafc;
    }

    body.dark-mode .export-btn {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }

    .clear-btn:hover,
    .export-btn:hover {
        opacity: 0.92;
    }

    .results-note {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 16px;
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
        min-width: 1280px;
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

    .hidden-row {
        display: none;
    }

    @media (max-width: 1100px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }

        .clear-btn,
        .export-btn {
            width: 100%;
        }
    }

    @media (max-width: 700px) {
        .filters-grid {
            grid-template-columns: 1fr;
        }
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

        $accountOptions = [];
        foreach (($transactions ?? []) as $transaction) {
            $accountNumber = $transaction['account']['accountNumber'] ?? null;
            if ($accountNumber && !in_array($accountNumber, $accountOptions)) {
                $accountOptions[] = $accountNumber;
            }
        }
        sort($accountOptions);
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
        <p class="section-note">Filter transactions by keyword, type, status, account, or date.</p>

        @if(count($transactions) > 0)
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label" for="searchFilter">Search</label>
                    <input
                        type="text"
                        id="searchFilter"
                        class="filter-input"
                        placeholder="Search reference, institution, beneficiary, description..."
                    >
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="typeFilter">Transfer Type</label>
                    <select id="typeFilter" class="filter-select">
                        <option value="">All Types</option>
                        <option value="internal">Internal</option>
                        <option value="localbank">Local Bank</option>
                        <option value="billpayment">Bill Payment</option>
                        <option value="deposit">Deposit</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="statusFilter">Status</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="accountFilter">Account</label>
                    <select id="accountFilter" class="filter-select">
                        <option value="">All Accounts</option>
                        @foreach($accountOptions as $accountNumber)
                            <option value="{{ strtolower($accountNumber) }}">{{ $accountNumber }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="dateFilter">Date</label>
                    <input type="date" id="dateFilter" class="filter-input">
                </div>

                <div class="filter-group">
                    <label class="filter-label">&nbsp;</label>
                    <button type="button" id="clearFilters" class="clear-btn">Clear Filters</button>
                </div>

                <div class="filter-group">
                    <label class="filter-label">&nbsp;</label>
                    <button type="button" id="exportCsvBtn" class="export-btn">Export CSV</button>
                </div>
            </div>

            <div class="results-note">
                Showing <strong id="visibleCount">{{ $transactionCount }}</strong> of <strong>{{ $transactionCount }}</strong> transactions.
            </div>

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
                    <tbody id="transactionsTableBody">
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
                                $accountNumber = $transaction['account']['accountNumber'] ?? '-';

                                $transferTypeLower = strtolower($transferType);
                                $dateValue = !empty($transaction['transactionDate'])
                                    ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('Y-m-d')
                                    : '';
                            @endphp

                            <tr
                                class="transaction-row"
                                data-search="{{ strtolower(
                                    ($transaction['referenceNumber'] ?? '') . ' ' .
                                    ($transactionType ?? '') . ' ' .
                                    ($transferType ?? '') . ' ' .
                                    ($institution ?? '') . ' ' .
                                    ($beneficiary ?? '') . ' ' .
                                    ($destinationAccount ?? '') . ' ' .
                                    ($transaction['description'] ?? '') . ' ' .
                                    ($transaction['remarks'] ?? '') . ' ' .
                                    ($accountNumber ?? '')
                                ) }}"
                                data-type="{{ strtolower($transferType) }}"
                                data-status="{{ strtolower($transaction['transactionStatus'] ?? 'completed') }}"
                                data-account="{{ strtolower($accountNumber) }}"
                                data-date="{{ $dateValue }}"
                                data-reference="{{ $transaction['referenceNumber'] ?? '-' }}"
                                data-account-number="{{ $accountNumber }}"
                                data-transaction-type="{{ $transactionType }}"
                                data-transfer-type="{{ $transferType }}"
                                data-institution="{{ $institution }}"
                                data-beneficiary="{{ $beneficiary }}"
                                data-destination="{{ $destinationAccount }}"
                                data-amount="{{ number_format($amount, 2, '.', '') }}"
                                data-status-text="{{ $transaction['transactionStatus'] ?? 'Completed' }}"
                                data-date-text="{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') : '-' }}"
                                data-time-text="{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('h:i:s A') : '-' }}"
                                data-description="{{ $transaction['description'] ?? '-' }}"
                                data-remarks="{{ $transaction['remarks'] ?? 'No additional remarks' }}"
                                data-direction="{{ $isIncoming ? 'Incoming' : 'Outgoing' }}"
                            >
                                <td>
                                    <div class="tx-main">{{ $transaction['referenceNumber'] ?? '-' }}</div>
                                    <div class="tx-subtext">Ref No.</div>
                                </td>

                                <td>
                                    <div class="tx-main">{{ $accountNumber }}</div>
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

@push('scripts')
<script>
    const searchFilter = document.getElementById('searchFilter');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const accountFilter = document.getElementById('accountFilter');
    const dateFilter = document.getElementById('dateFilter');
    const clearFilters = document.getElementById('clearFilters');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const rows = document.querySelectorAll('.transaction-row');
    const visibleCount = document.getElementById('visibleCount');

    function applyTransactionFilters() {
        const searchValue = (searchFilter?.value || '').toLowerCase().trim();
        const typeValue = (typeFilter?.value || '').toLowerCase();
        const statusValue = (statusFilter?.value || '').toLowerCase();
        const accountValue = (accountFilter?.value || '').toLowerCase();
        const dateValue = (dateFilter?.value || '').toLowerCase();

        let count = 0;

        rows.forEach((row) => {
            const rowSearch = row.dataset.search || '';
            const rowType = row.dataset.type || '';
            const rowStatus = row.dataset.status || '';
            const rowAccount = row.dataset.account || '';
            const rowDate = row.dataset.date || '';

            const matchesSearch = !searchValue || rowSearch.includes(searchValue);
            const matchesType = !typeValue || rowType === typeValue;
            const matchesStatus = !statusValue || rowStatus === statusValue;
            const matchesAccount = !accountValue || rowAccount === accountValue;
            const matchesDate = !dateValue || rowDate === dateValue;

            const isVisible = matchesSearch && matchesType && matchesStatus && matchesAccount && matchesDate;

            row.classList.toggle('hidden-row', !isVisible);

            if (isVisible) {
                count++;
            }
        });

        if (visibleCount) {
            visibleCount.textContent = count;
        }
    }

    function csvEscape(value) {
        const stringValue = String(value ?? '');
        return `"${stringValue.replace(/"/g, '""')}"`;
    }

    function exportVisibleTransactionsToCsv() {
        const visibleRows = Array.from(document.querySelectorAll('.transaction-row'))
            .filter(row => !row.classList.contains('hidden-row'));

        if (visibleRows.length === 0) {
            alert('There are no filtered transactions to export.');
            return;
        }

        const headers = [
            'Reference Number',
            'Account Number',
            'Transaction Type',
            'Transfer Type',
            'Institution / Biller',
            'Beneficiary',
            'Destination Account / Reference',
            'Amount',
            'Direction',
            'Transaction Status',
            'Date',
            'Time',
            'Description',
            'Remarks'
        ];

        const csvRows = [headers.map(csvEscape).join(',')];

        visibleRows.forEach((row) => {
            const data = [
                row.dataset.reference,
                row.dataset.accountNumber,
                row.dataset.transactionType,
                row.dataset.transferType,
                row.dataset.institution,
                row.dataset.beneficiary,
                row.dataset.destination,
                row.dataset.amount,
                row.dataset.direction,
                row.dataset.statusText,
                row.dataset.dateText,
                row.dataset.timeText,
                row.dataset.description,
                row.dataset.remarks
            ];

            csvRows.push(data.map(csvEscape).join(','));
        });

        const csvContent = csvRows.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);

        const now = new Date();
        const timestamp = [
            now.getFullYear(),
            String(now.getMonth() + 1).padStart(2, '0'),
            String(now.getDate()).padStart(2, '0'),
            '-',
            String(now.getHours()).padStart(2, '0'),
            String(now.getMinutes()).padStart(2, '0'),
            String(now.getSeconds()).padStart(2, '0')
        ].join('');

        const link = document.createElement('a');
        link.href = url;
        link.download = `bof-transactions-${timestamp}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    [searchFilter, typeFilter, statusFilter, accountFilter, dateFilter].forEach((element) => {
        if (element) {
            element.addEventListener('input', applyTransactionFilters);
            element.addEventListener('change', applyTransactionFilters);
        }
    });

    if (clearFilters) {
        clearFilters.addEventListener('click', () => {
            if (searchFilter) searchFilter.value = '';
            if (typeFilter) typeFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            if (accountFilter) accountFilter.value = '';
            if (dateFilter) dateFilter.value = '';
            applyTransactionFilters();
        });
    }

    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', exportVisibleTransactionsToCsv);
    }

    applyTransactionFilters();
</script>
@endpush