@extends('layouts.app')

@php
    $pageTitle = 'Account Statement - BoF Online Banking';
@endphp

@section('topcard')
    <div class="stmt-hero">
        <div class="stmt-hero-copy">
            <div class="stmt-hero-eyebrow">Document Services</div>
            <h2>Account Statement</h2>
            <p>Search and download account statements by account, type, and date range.</p>
        </div>
    </div>
@endsection

@push('styles')
<style>
    :root {
        --stmt-surface: rgba(255,255,255,0.92);
        --stmt-surface-2: #f8fbff;
        --stmt-border: rgba(226, 232, 240, 0.95);
        --stmt-text: var(--text-main);
        --stmt-soft: var(--text-soft);
        --stmt-primary: var(--primary-light);
        --stmt-primary-dark: var(--primary-mid);
        --stmt-title: #b83232;
        --stmt-shadow: var(--shadow-soft);
    }

    body.dark-mode {
        --stmt-surface: rgba(17,24,39,0.92);
        --stmt-surface-2: #111b2e;
        --stmt-border: rgba(51,65,85,0.95);
        --stmt-text: #f3f4f6;
        --stmt-soft: #cbd5e1;
        --stmt-primary: #7eb2ff;
        --stmt-primary-dark: #8fb8ff;
        --stmt-title: #ff9b9b;
    }

    .stmt-hero h2 {
        margin: 0 0 10px;
        color: var(--primary-mid);
        font-size: 2rem;
        font-weight: 800;
    }

    .stmt-hero p {
        margin: 0;
        color: var(--text-soft);
        font-size: 1rem;
        line-height: 1.7;
    }

    .stmt-hero-eyebrow {
        display: inline-block;
        margin-bottom: 10px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.10);
        color: var(--primary-mid);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stmt-card {
        background: var(--stmt-surface);
        border: 1px solid var(--stmt-border);
        border-radius: 22px;
        box-shadow: var(--stmt-shadow);
        overflow: hidden;
    }

    .stmt-head {
        padding: 22px 24px 14px;
        border-bottom: 1px solid var(--stmt-border);
    }

    .stmt-head h3 {
        margin: 0;
        text-align: center;
        color: var(--stmt-title);
        font-size: 1.15rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .stmt-filters {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr auto;
        gap: 0;
        margin: 24px;
        border: 1px solid var(--stmt-border);
        border-radius: 12px;
        overflow: hidden;
        background: var(--stmt-surface);
    }

    .stmt-top-select {
        width: 100%;
        min-height: 48px;
        border: 0;
        border-right: 1px solid var(--stmt-border);
        background: var(--stmt-surface);
        color: var(--stmt-text);
        padding: 0 14px;
        font-size: 0.96rem;
        outline: none;
    }

    .stmt-filters > *:last-child {
        border-right: 0;
    }

    .stmt-date-row {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        padding: 0 24px 24px;
    }

    .stmt-date-block {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stmt-date-label {
        font-weight: 800;
        color: var(--stmt-text);
        font-size: 0.95rem;
        text-transform: uppercase;
    }

    .stmt-date-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .stmt-date-input {
        min-width: 170px;
        height: 42px;
        padding: 0 44px 0 14px;
        border: 1px solid var(--stmt-border);
        border-radius: 10px;
        background: var(--stmt-surface);
        color: var(--stmt-text);
        font-size: 0.96rem;
        outline: none;
    }

    .stmt-date-input:focus,
    .stmt-top-select:focus,
    .stmt-search-input:focus {
        border-color: var(--stmt-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
    }

    .stmt-date-icon {
        position: absolute;
        right: 12px;
        width: 18px;
        height: 18px;
        pointer-events: none;
        color: var(--stmt-primary-dark);
    }

    .stmt-search-row {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--stmt-soft);
        font-size: 0.96rem;
    }

    .stmt-search-input {
        height: 42px;
        min-width: 220px;
        border: 1px solid var(--stmt-border);
        border-radius: 10px;
        background: var(--stmt-surface);
        color: var(--stmt-text);
        padding: 0 14px;
        outline: none;
    }

    .stmt-reset-btn {
        border: 0;
        background: transparent;
        color: var(--stmt-soft);
        cursor: pointer;
        font-size: 0.96rem;
    }

    .stmt-reset-btn:hover {
        color: var(--stmt-primary-dark);
    }

    .stmt-table-wrap {
        padding: 0 24px 24px;
    }

    .stmt-table {
        width: 100%;
        border-collapse: collapse;
    }

    .stmt-table th,
    .stmt-table td {
        padding: 16px 10px;
        border-bottom: 1px solid var(--stmt-border);
        text-align: left;
        color: var(--stmt-text);
    }

    .stmt-table th {
        font-size: 0.95rem;
        color: var(--stmt-soft);
        font-weight: 500;
    }

    .stmt-group-row td {
        padding-top: 20px;
        padding-bottom: 14px;
        font-size: 1rem;
        font-weight: 800;
        color: var(--stmt-text);
        background: transparent;
    }

    .stmt-icon-cell {
        width: 56px;
    }

    .stmt-doc-icon {
        width: 30px;
        height: 30px;
        color: var(--stmt-text);
        opacity: 0.9;
    }

    .stmt-link {
        color: var(--stmt-primary-dark);
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
    }

    .stmt-date-text {
        color: var(--stmt-primary-dark);
        font-size: 1rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .stmt-download-cell {
        width: 70px;
        text-align: right;
    }

    .stmt-download-btn {
        border: 0;
        background: transparent;
        color: var(--stmt-primary-dark);
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .stmt-download-btn:hover {
        background: rgba(37, 99, 235, 0.08);
    }

    .stmt-empty {
        padding: 30px 24px;
        color: var(--stmt-soft);
    }

    .stmt-hidden {
        display: none !important;
    }

    @media (max-width: 1100px) {
        .stmt-filters {
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--stmt-border);
        }

        .stmt-top-select {
            border-right: 0;
        }
    }

    @media (max-width: 760px) {
        .stmt-date-row {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }

        .stmt-date-block {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }

        .stmt-search-row {
            flex-wrap: wrap;
        }

        .stmt-search-input {
            min-width: 100%;
        }

        .stmt-table-wrap {
            overflow-x: auto;
        }

        .stmt-table {
            min-width: 760px;
        }
    }
</style>
@endpush

@section('content')
<div class="stmt-card">
    <div class="stmt-head">
        <h3>Account Statement</h3>
    </div>

    <div class="stmt-filters">
        <select id="stmtAccountFilter" class="stmt-top-select">
            <option value="">All Accounts</option>
            <option value="044145600017">044145600017</option>
        </select>

        <select id="stmtTypeFilter" class="stmt-top-select">
            <option value="">All Types</option>
            <option value="monthly">Monthly</option>
        </select>

        <select id="stmtMonthFilter" class="stmt-top-select">
            <option value="">All Periods</option>
            <option value="february-2026">February-2026</option>
            <option value="january-2026">January-2026</option>
        </select>

        <select id="stmtSortFilter" class="stmt-top-select">
            <option value="desc">Newest First</option>
            <option value="asc">Oldest First</option>
        </select>
    </div>

    <div class="stmt-date-row">
        <div class="stmt-date-block">
            <span class="stmt-date-label">Start</span>
            <div class="stmt-date-input-wrap">
                <input type="date" id="stmtStartDate" class="stmt-date-input" value="2026-01-19">
                <svg class="stmt-date-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                </svg>
            </div>
        </div>

        <div class="stmt-date-block">
            <span class="stmt-date-label">End</span>
            <div class="stmt-date-input-wrap">
                <input type="date" id="stmtEndDate" class="stmt-date-input" value="2026-03-20">
                <svg class="stmt-date-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                </svg>
            </div>
        </div>

        <div class="stmt-search-row">
            <input type="text" id="stmtSearch" class="stmt-search-input" placeholder="Search">
            <button type="button" id="stmtResetBtn" class="stmt-reset-btn">Reset</button>
        </div>
    </div>

    <div class="stmt-table-wrap">
        @if(count($statements))
            <table class="stmt-table">
                <thead>
                    <tr>
                        <th style="width:56px;"></th>
                        <th>Document name</th>
                        <th>Date</th>
                        <th style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody id="stmtTableBody">
                    @foreach($statements as $group)
                        <tr class="stmt-group-row" data-group="{{ strtolower($group['group']) }}">
                            <td></td>
                            <td colspan="3">{{ $group['group'] }}</td>
                        </tr>

                        @foreach($group['items'] as $item)
                            <tr
                                class="stmt-item-row"
                                data-group="{{ strtolower($group['group']) }}"
                                data-account="{{ strtolower($item['account']) }}"
                                data-type="{{ strtolower($item['type']) }}"
                                data-date="{{ $item['date'] }}"
                                data-search="{{ strtolower($item['name']) }}"
                            >
                                <td class="stmt-icon-cell">
                                    <svg class="stmt-doc-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                                        <path d="M14 3v6h6"></path>
                                        <path d="M9 13h6M9 17h6M9 9h2"></path>
                                    </svg>
                                </td>

                                <td>
                                    <a href="{{ route('account-statement.preview', $item['id']) }}" target="_blank" class="stmt-link">
                                        {{ $item['name'] }}
                                    </a>
                                </td>

                                <td class="stmt-date-text">
                                    {{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}
                                </td>

                                <td class="stmt-download-cell">
                                    <a href="{{ route('account-statement.download', $item['id']) }}" class="stmt-download-btn" title="Download statement"></a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div id="stmtEmpty" class="stmt-empty stmt-hidden">
                No statements match the selected filters.
            </div>
        @else
            <div class="stmt-empty">No account statements available.</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const accountFilter = document.getElementById('stmtAccountFilter');
        const typeFilter = document.getElementById('stmtTypeFilter');
        const monthFilter = document.getElementById('stmtMonthFilter');
        const sortFilter = document.getElementById('stmtSortFilter');
        const startDate = document.getElementById('stmtStartDate');
        const endDate = document.getElementById('stmtEndDate');
        const searchInput = document.getElementById('stmtSearch');
        const resetBtn = document.getElementById('stmtResetBtn');
        const emptyState = document.getElementById('stmtEmpty');

        const itemRows = Array.from(document.querySelectorAll('.stmt-item-row'));
        const groupRows = Array.from(document.querySelectorAll('.stmt-group-row'));
        const tableBody = document.getElementById('stmtTableBody');

        function parseDate(value) {
            return value ? new Date(value + 'T00:00:00') : null;
        }

        function applyFilters() {
            const accountValue = (accountFilter?.value || '').toLowerCase();
            const typeValue = (typeFilter?.value || '').toLowerCase();
            const monthValue = (monthFilter?.value || '').toLowerCase();
            const searchValue = (searchInput?.value || '').toLowerCase().trim();
            const startValue = parseDate(startDate?.value || '');
            const endValue = parseDate(endDate?.value || '');

            let visibleCount = 0;
            const visibleGroups = new Set();

            itemRows.forEach(row => {
                const rowAccount = (row.dataset.account || '').toLowerCase();
                const rowType = (row.dataset.type || '').toLowerCase();
                const rowGroup = (row.dataset.group || '').toLowerCase();
                const rowSearch = (row.dataset.search || '').toLowerCase();
                const rowDateRaw = row.dataset.date || '';
                const rowDate = parseDate(rowDateRaw);

                const matchesAccount = !accountValue || rowAccount === accountValue;
                const matchesType = !typeValue || rowType === typeValue;
                const matchesMonth = !monthValue || rowGroup === monthValue;
                const matchesSearch = !searchValue || rowSearch.includes(searchValue);

                let matchesDate = true;
                if (startValue && rowDate && rowDate < startValue) matchesDate = false;
                if (endValue && rowDate && rowDate > endValue) matchesDate = false;

                const show = matchesAccount && matchesType && matchesMonth && matchesSearch && matchesDate;
                row.classList.toggle('stmt-hidden', !show);

                if (show) {
                    visibleCount++;
                    visibleGroups.add(rowGroup);
                }
            });

            groupRows.forEach(groupRow => {
                const groupKey = (groupRow.dataset.group || '').toLowerCase();
                groupRow.classList.toggle('stmt-hidden', !visibleGroups.has(groupKey));
            });

            if (emptyState) {
                emptyState.classList.toggle('stmt-hidden', visibleCount !== 0);
            }

            applySort();
        }

        function applySort() {
            if (!tableBody) return;

            const groups = groupRows
                .map(groupRow => {
                    const groupKey = groupRow.dataset.group || '';
                    const relatedItems = itemRows.filter(row => (row.dataset.group || '') === groupKey);
                    return { groupRow, relatedItems };
                })
                .filter(group => !group.groupRow.classList.contains('stmt-hidden'));

            groups.sort((a, b) => {
                const aDate = a.relatedItems[0]?.dataset.date || '';
                const bDate = b.relatedItems[0]?.dataset.date || '';
                return sortFilter?.value === 'asc'
                    ? aDate.localeCompare(bDate)
                    : bDate.localeCompare(aDate);
            });

            groups.forEach(group => {
                tableBody.appendChild(group.groupRow);
                group.relatedItems.forEach(item => tableBody.appendChild(item));
            });
        }

        [accountFilter, typeFilter, monthFilter, sortFilter, startDate, endDate].forEach(el => {
            if (el) el.addEventListener('change', applyFilters);
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (accountFilter) accountFilter.value = '';
                if (typeFilter) typeFilter.value = '';
                if (monthFilter) monthFilter.value = '';
                if (sortFilter) sortFilter.value = 'desc';
                if (startDate) startDate.value = '2026-01-19';
                if (endDate) endDate.value = '2026-03-20';
                if (searchInput) searchInput.value = '';
                applyFilters();
            });
        }

        applyFilters();
    })();
</script>
@endpush