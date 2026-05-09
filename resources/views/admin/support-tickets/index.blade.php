@extends('admin.layout')

@section('heading', 'Support Tickets')
@section('subheading', 'Review customer support tickets and manage ticket status.')

@section('admin-content')
    <section class="admin-card">
        <form method="GET" action="{{ route('admin.support-tickets.index') }}" class="admin-form-grid">
            <div class="admin-field">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ticket, customer, email, subject">
            </div>
            <div class="admin-field">
                <label for="ticketStatus">Status</label>
                <select id="ticketStatus" name="ticketStatus">
                    <option value="">All statuses</option>
                    @foreach(['Open', 'InProgress', 'Resolved', 'Unresolved'] as $status)
                        <option value="{{ $status }}" @selected(($filters['ticketStatus'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="satisfactionRating">Rating</label>
                <select id="satisfactionRating" name="satisfactionRating">
                    <option value="">All ratings</option>
                    @foreach([5, 4, 3, 2, 1] as $rating)
                        <option value="{{ $rating }}" @selected((string) ($filters['satisfactionRating'] ?? '') === (string) $rating)>{{ $rating }} stars</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="createdAt" @selected(($filters['sort'] ?? 'createdAt') === 'createdAt')>Created date</option>
                    <option value="ticketStatus" @selected(($filters['sort'] ?? '') === 'ticketStatus')>Status</option>
                </select>
            </div>
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Apply Filters</button>
                <a class="admin-btn secondary" href="{{ route('admin.support-tickets.index') }}">Reset</a>
            </div>
        </form>
    </section>

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
                        <th>Bot Assisted</th>
                        <th>Rating</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        @php
                            $status = $ticket['ticketStatus'] ?? '-';
                            $statusClass = match ($status) {
                                'Open' => 'admin-badge-blue',
                                'InProgress' => 'admin-badge-yellow',
                                'Resolved' => 'admin-badge-green',
                                'Unresolved' => 'admin-badge-red',
                                default => 'admin-badge-slate',
                            };
                            $botAssisted = !empty($ticket['consultantReply']) && in_array($status, ['InProgress', 'Resolved'], true);
                        @endphp
                        <tr>
                            <td>{{ $ticket['ticketNumber'] ?? '-' }}</td>
                            <td>{{ $ticket['customerName'] ?? '-' }}</td>
                            <td>{{ $ticket['customerEmail'] ?? '-' }}</td>
                            <td>{{ $ticket['subject'] ?? '-' }}</td>
                            <td><span class="admin-badge {{ $statusClass }}">{{ $status }}</span></td>
                            <td>
                                @if($botAssisted)
                                    <span class="admin-badge admin-badge-blue">Bot Assisted</span>
                                @elseif($status === 'Open')
                                    <span class="admin-badge admin-badge-yellow">Needs Consultant</span>
                                @else
                                    <span class="admin-badge admin-badge-slate">Manual</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($ticket['satisfactionRating']))
                                    <span class="admin-badge admin-badge-green">Rated {{ $ticket['satisfactionRating'] }}/5</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ !empty($ticket['createdAt']) ? \Carbon\Carbon::parse($ticket['createdAt'])->format('d M Y') : '-' }}</td>
                            <td><a class="admin-btn secondary" href="{{ route('admin.support-tickets.show', $ticket['documentId'] ?? $ticket['id']) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No support tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
