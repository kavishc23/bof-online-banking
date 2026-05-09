@extends('admin.layout')

@section('heading', 'Loan Applications')
@section('subheading', 'Read-only view of loan applications submitted through online banking.')

@section('admin-content')
    <section class="admin-card">
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer Email</th>
                        <th>Loan Type</th>
                        <th>Amount</th>
                        <th>Months</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan['referenceNumber'] ?? '-' }}</td>
                            <td>{{ $loan['customerEmail'] ?? '-' }}</td>
                            <td>{{ $loan['loanType'] ?? '-' }}</td>
                            <td>{{ number_format((float) ($loan['amountRequested'] ?? 0), 2) }}</td>
                            <td>{{ $loan['repaymentMonths'] ?? '-' }}</td>
                            <td>{{ $loan['applicationStatus'] ?? '-' }}</td>
                            <td>{{ !empty($loan['submittedAt']) ? \Carbon\Carbon::parse($loan['submittedAt'])->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

