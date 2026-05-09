@extends('admin.layout')

@section('heading', 'Account Management')
@section('subheading', 'View and maintain Strapi account records.')
@section('actions')
    <a href="{{ route('admin.accounts.create') }}" class="admin-btn">Create Account</a>
@endsection

@section('admin-content')
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
                            <td>{{ $account['accountType'] ?? '-' }}</td>
                            <td>{{ number_format((float) ($account['balance'] ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($account['monthlyMaintenanceFee'] ?? 0), 2) }}</td>
                            <td>{{ $account['interestRate'] ?? '0' }}</td>
                            <td>{{ !empty($account['openedAt']) ? \Carbon\Carbon::parse($account['openedAt'])->format('d M Y') : '-' }}</td>
                            <td>{{ $account['customer']['email'] ?? $account['customer']['id'] ?? '-' }}</td>
                            <td><a class="admin-btn secondary" href="{{ route('admin.accounts.edit', $account['documentId'] ?? $account['id']) }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

