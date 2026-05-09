@extends('admin.layout')

@section('heading', 'Admin Dashboard')
@section('subheading', 'Operational summary for accounts, support tickets, transactions, and notifications.')

@section('admin-content')
    <section class="admin-grid">
        @foreach([
            'Total accounts' => $summary['total_accounts'] ?? 0,
            'Total transactions' => $summary['total_transactions'] ?? 0,
            'Loan applications' => $summary['total_loan_applications'] ?? 0,
            'Pending loans' => $summary['pending_loan_applications'] ?? 0,
            'Total support tickets' => $summary['total_support_tickets'] ?? 0,
            'Open support tickets' => $summary['open_support_tickets'] ?? 0,
            'Unresolved support tickets' => $summary['unresolved_support_tickets'] ?? 0,
            'Enabled notifications' => $summary['enabled_notifications'] ?? 0,
        ] as $label => $value)
            <div class="admin-card admin-stat">
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
            </div>
        @endforeach
    </section>
@endsection
