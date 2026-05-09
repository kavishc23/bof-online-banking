@extends('admin.layout')

@section('heading', 'All Transactions')
@section('subheading', 'Read-only view of customer transactions from the Strapi backend.')

@section('admin-content')
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ !empty($transaction['transactionDate']) ? \Carbon\Carbon::parse($transaction['transactionDate'])->format('d M Y') : '-' }}</td>
                            <td>{{ $transaction['referenceNumber'] ?? '-' }}</td>
                            <td>{{ $transaction['transactionType'] ?? '-' }}</td>
                            <td>{{ number_format((float) ($transaction['amount'] ?? 0), 2) }}</td>
                            <td>{{ $transaction['transactionStatus'] ?? '-' }}</td>
                            <td>
                                {{ $transaction['account']['accountNumber']
                                    ?? $transaction['sourceAccount']['accountNumber']
                                    ?? $transaction['destinationAccount']['accountNumber']
                                    ?? '-' }}
                            </td>
                            <td>{{ $transaction['description'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

