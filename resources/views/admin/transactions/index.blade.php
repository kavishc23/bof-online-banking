@extends('admin.layout')

@section('heading', 'All Transactions')
@section('subheading', 'Read-only view of customer transactions from the Strapi backend.')

@section('admin-content')
    <section class="admin-card">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="admin-form-grid">
            <div class="admin-field">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, description, beneficiary">
            </div>
            <div class="admin-field">
                <label for="transactionType">Type</label>
                <select id="transactionType" name="transactionType">
                    <option value="">All types</option>
                    @foreach(['Deposit', 'Withdrawal', 'Transfer', 'BillPayment', 'Fee'] as $type)
                        <option value="{{ $type }}" @selected(($filters['transactionType'] ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="transactionStatus">Status</label>
                <input id="transactionStatus" name="transactionStatus" value="{{ $filters['transactionStatus'] ?? '' }}" placeholder="Completed">
            </div>
            <div class="admin-field">
                <label for="startDate">Start Date</label>
                <input id="startDate" name="startDate" type="date" value="{{ $filters['startDate'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label for="endDate">End Date</label>
                <input id="endDate" name="endDate" type="date" value="{{ $filters['endDate'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label for="minAmount">Min Amount</label>
                <input id="minAmount" name="minAmount" type="number" step="0.01" value="{{ $filters['minAmount'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label for="maxAmount">Max Amount</label>
                <input id="maxAmount" name="maxAmount" type="number" step="0.01" value="{{ $filters['maxAmount'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    @foreach(['transactionDate' => 'Date', 'amount' => 'Amount', 'transactionType' => 'Type', 'transactionStatus' => 'Status'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'transactionDate') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Apply Filters</button>
                <a class="admin-btn secondary" href="{{ route('admin.transactions.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Account</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') : '-' }}</td>
                            <td>{{ $transaction['referenceNumber'] ?? '-' }}</td>
                            <td><span class="admin-badge admin-badge-blue">{{ $transaction['transactionType'] ?? '-' }}</span></td>
                            <td>{{ number_format((float) ($transaction['amount'] ?? 0), 2) }}</td>
                            <td><span class="admin-badge admin-badge-green">{{ $transaction['transactionStatus'] ?? '-' }}</span></td>
                            <td>
                                {{ $transaction['account']['accountNumber']
                                    ?? $transaction['sourceAccount']['accountNumber']
                                    ?? $transaction['destinationAccount']['accountNumber']
                                    ?? '-' }}
                            </td>
                            <td>{{ $transaction['description'] ?? '-' }}</td>
                            <td><a class="admin-btn secondary" href="{{ route('admin.transactions.show', $transaction['documentId'] ?? $transaction['id']) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
