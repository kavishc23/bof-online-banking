@extends('admin.layout')

@section('heading', 'Support Ticket Details')
@section('subheading', $ticket['ticketNumber'] ?? 'Support ticket')

@section('admin-content')
    <section class="admin-card">
        <div class="admin-grid">
            <div><strong>Customer</strong><br>{{ $ticket['customerName'] ?? '-' }}</div>
            <div><strong>Email</strong><br>{{ $ticket['customerEmail'] ?? '-' }}</div>
            <div><strong>Subject</strong><br>{{ $ticket['subject'] ?? '-' }}</div>
            <div><strong>Status</strong><br>{{ $ticket['ticketStatus'] ?? '-' }}</div>
            <div><strong>Rating</strong><br>{{ $ticket['satisfactionRating'] ?? '-' }}</div>
            <div><strong>Resolved At</strong><br>{{ !empty($ticket['resolvedAt']) ? \Carbon\Carbon::parse($ticket['resolvedAt'])->format('d M Y H:i') : '-' }}</div>
        </div>
    </section>

    <section class="admin-card">
        <h3>Customer Message</h3>
        <p>{{ $ticket['message'] ?? '-' }}</p>
        @if(!empty($ticket['satisfactionComment']))
            <h3>Satisfaction Comment</h3>
            <p>{{ $ticket['satisfactionComment'] }}</p>
        @endif
    </section>

    <section class="admin-card">
        <form method="POST" action="{{ route('admin.support-tickets.update', $ticket['documentId'] ?? $ticket['id']) }}" class="admin-form-grid">
            @csrf
            @method('PATCH')
            <div class="admin-field full">
                <label for="consultantReply">Consultant Reply</label>
                <textarea id="consultantReply" name="consultantReply" rows="6">{{ old('consultantReply', $ticket['consultantReply'] ?? '') }}</textarea>
            </div>
            <div class="admin-field">
                <label for="ticketStatus">Ticket Status</label>
                <select id="ticketStatus" name="ticketStatus" required>
                    @foreach(['Open', 'InProgress', 'Resolved', 'Unresolved'] as $status)
                        <option value="{{ $status }}" @selected(old('ticketStatus', $ticket['ticketStatus'] ?? 'Open') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Save Ticket</button>
                <a class="admin-btn secondary" href="{{ route('admin.support-tickets.index') }}">Back</a>
            </div>
        </form>
    </section>
@endsection

