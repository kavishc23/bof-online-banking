@extends('admin.layout')

@section('heading', 'Support Tickets')
@section('subheading', 'Review customer support tickets and manage ticket status.')

@section('admin-content')
    <section class="admin-card">
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket['ticketNumber'] ?? '-' }}</td>
                            <td>{{ $ticket['customerName'] ?? '-' }}</td>
                            <td>{{ $ticket['customerEmail'] ?? '-' }}</td>
                            <td>{{ $ticket['subject'] ?? '-' }}</td>
                            <td>{{ $ticket['ticketStatus'] ?? '-' }}</td>
                            <td>{{ $ticket['satisfactionRating'] ?? '-' }}</td>
                            <td>{{ !empty($ticket['createdAt']) ? \Carbon\Carbon::parse($ticket['createdAt'])->format('d M Y') : '-' }}</td>
                            <td><a class="admin-btn secondary" href="{{ route('admin.support-tickets.show', $ticket['documentId'] ?? $ticket['id']) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No support tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

