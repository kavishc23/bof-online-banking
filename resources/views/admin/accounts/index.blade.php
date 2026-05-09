@extends('admin.layout')

@section('heading', 'Account Management')
@section('subheading', 'View and maintain Strapi account records.')
@section('actions')
    <a href="{{ route('admin.accounts.create') }}" class="admin-btn">Create Account</a>
@endsection

@section('admin-content')
    <section class="admin-card">
        <form method="GET" action="{{ route('admin.accounts.index') }}" class="admin-form-grid">
            <div class="admin-field">
                <label for="search">Search Account Number</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="e.g. 1001">
            </div>
            <div class="admin-field">
                <label for="accountType">Account Type</label>
                <select id="accountType" name="accountType">
                    <option value="">All types</option>
                    @foreach(['SimpleAccess', 'Savings', 'Business'] as $type)
                        <option value="{{ $type }}" @selected(($filters['accountType'] ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="minBalance">Min Balance</label>
                <input id="minBalance" name="minBalance" type="number" step="0.01" value="{{ $filters['minBalance'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label for="maxBalance">Max Balance</label>
                <input id="maxBalance" name="maxBalance" type="number" step="0.01" value="{{ $filters['maxBalance'] ?? '' }}">
            </div>
            <div class="admin-field">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    @foreach(['accountNumber' => 'Account Number', 'accountType' => 'Account Type', 'balance' => 'Balance', 'openedAt' => 'Opened At'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'accountNumber') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="direction">Direction</label>
                <select id="direction" name="direction">
                    <option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>Ascending</option>
                    <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>Descending</option>
                </select>
            </div>
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Apply Filters</button>
                <a class="admin-btn secondary" href="{{ route('admin.accounts.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Type</th>
                        <th>Balance</th>
                        <th>Monthly Fee</th>
                        <th>Interest</th>
                        <th>Opened</th>
                        <th>Customer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account['accountNumber'] ?? '-' }}</td>
                            <td><span class="admin-badge admin-badge-blue">{{ $account['accountType'] ?? '-' }}</span></td>
                            <td>{{ number_format((float) ($account['balance'] ?? 0), 2) }}</td>
                            <td>
                                {{ number_format((float) ($account['feeSummary']['calculated_monthly_fee'] ?? $account['monthlyMaintenanceFee'] ?? 0), 2) }}
                                @if(!empty($account['feeSummary']['explanation']))
                                    <div style="color:var(--text-soft); font-size:12px;">{{ $account['feeSummary']['explanation'] }}</div>
                                @endif
                            </td>
                            <td>{{ $account['interestRate'] ?? '0' }}</td>
                            <td>{{ !empty($account['openedAt']) ? \Carbon\Carbon::parse($account['openedAt'])->format('d M Y') : '-' }}</td>
                            <td>{{ $account['customer']['email'] ?? $account['customer']['firstName'] ?? $account['customer']['id'] ?? '-' }}</td>
                            <td>
                                <div class="admin-actions">
                                    <a class="admin-btn secondary" href="{{ route('admin.accounts.show', $account['documentId'] ?? $account['id']) }}">View</a>
                                    <a class="admin-btn secondary" href="{{ route('admin.accounts.edit', $account['documentId'] ?? $account['id']) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
