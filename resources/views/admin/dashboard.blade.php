@extends('admin.layout')

@section('heading', 'Admin Dashboard')
@section('subheading', 'Operational summary for accounts, support tickets, transactions, and notifications.')

@section('admin-content')
    <section class="admin-grid">
        @foreach([
            'Total accounts' => $summary['total_accounts'] ?? 0,
            'Total customers' => $summary['total_customers'] ?? 0,
            'Total transactions' => $summary['total_transactions'] ?? 0,
            'Deposits' => $summary['total_deposits'] ?? 0,
            'Withdrawals' => $summary['total_withdrawals'] ?? 0,
            'Transfers' => $summary['total_transfers'] ?? 0,
            'Bill payments' => $summary['total_bill_payments'] ?? 0,
            'Loan applications' => $summary['total_loan_applications'] ?? 0,
            'Pending loans' => $summary['pending_loan_applications'] ?? 0,
            'Support chats' => $summary['total_support_tickets'] ?? 0,
            'FAQ auto-resolved chats' => $summary['faq_auto_resolved_chats'] ?? 0,
            'Need consultant' => $summary['open_chats_needing_consultant'] ?? 0,
            'Open chats' => $summary['open_support_tickets'] ?? 0,
            'Unresolved chats' => $summary['unresolved_support_tickets'] ?? 0,
            'Active chatbot FAQs' => $summary['active_chatbot_faqs'] ?? 0,
            'Enabled notifications' => $summary['enabled_notifications'] ?? 0,
        ] as $label => $value)
            <div class="admin-card admin-stat">
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
            </div>
        @endforeach
    </section>

    <section class="admin-grid" style="margin-top: 22px;">
        <div class="admin-card">
            <h3>Latest Transactions</h3>
            <div class="admin-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['latest_transactions'] ?? [] as $transaction)
                            <tr>
                                <td>{{ $transaction['referenceNumber'] ?? '-' }}</td>
                                <td><span class="admin-badge admin-badge-blue">{{ $transaction['transactionType'] ?? '-' }}</span></td>
                                <td>{{ number_format((float) ($transaction['amount'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <h3>Latest Support Chats</h3>
            <div class="admin-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['latest_support_chats'] ?? [] as $ticket)
                            <tr>
                                <td>{{ $ticket['ticketNumber'] ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($ticket['subject'] ?? '-', 32) }}</td>
                                <td><span class="admin-badge admin-badge-slate">{{ $ticket['ticketStatus'] ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No support chats found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <h3>Latest Accounts</h3>
            <div class="admin-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['latest_accounts'] ?? [] as $account)
                            <tr>
                                <td>{{ $account['accountNumber'] ?? '-' }}</td>
                                <td><span class="admin-badge admin-badge-green">{{ $account['accountType'] ?? '-' }}</span></td>
                                <td>{{ number_format((float) ($account['balance'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
