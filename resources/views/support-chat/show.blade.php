@extends('layouts.app')

@php
    $pageTitle = 'Support Chat - BoF Online Banking';
    $status = $chat['ticketStatus'] ?? 'Open';
    $statusClass = match ($status) {
        'Resolved' => 'status-resolved',
        'Unresolved' => 'status-unresolved',
        'InProgress' => 'status-inprogress',
        default => 'status-open',
    };
    $botAssisted = !empty($chat['consultantReply'])
        && in_array($status, ['InProgress', 'Resolved'], true)
        && empty($chat['resolvedAt']) === false ? false : (!empty($chat['consultantReply']) && $status === 'InProgress');
@endphp

@section('topcard')
    <div class="chat-thread-hero">
        <div>
            <div class="chat-thread-meta">{{ $chat['ticketNumber'] ?? 'Support Chat' }}</div>
            <h2>{{ $chat['subject'] ?? 'Support conversation' }}</h2>
            <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
        </div>
        <a href="{{ route('support-chat.index') }}" class="chat-secondary-btn">All Chats</a>
    </div>
@endsection

@push('styles')
<style>
    .chat-thread-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        position: relative;
        z-index: 1;
    }
    .chat-thread-meta {
        color: var(--primary-light);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .chat-thread-hero h2 { margin: 0 0 12px; color: var(--primary-mid); font-size: 2rem; font-weight: 800; }
    .chat-secondary-btn, .chat-submit-btn, .chat-outline-btn {
        display: inline-flex;
        border: none;
        border-radius: 12px;
        padding: 11px 15px;
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
    }
    .chat-outline-btn { background: rgba(37, 99, 235, 0.08); color: var(--primary-mid); }
    .status-badge {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .status-open { background: #dbeafe; color: #1d4ed8; }
    .status-inprogress { background: #fef3c7; color: #92400e; }
    .status-resolved { background: #dcfce7; color: #166534; }
    .status-unresolved { background: #fee2e2; color: #991b1b; }
    .chat-window {
        background: var(--card-bg);
        border: 1px solid var(--border-soft);
        border-radius: 22px;
        box-shadow: var(--shadow-soft);
        padding: 22px;
        display: grid;
        gap: 18px;
    }
    .chat-bubble-row { display: flex; }
    .chat-bubble-row.customer { justify-content: flex-end; }
    .chat-bubble-row.consultant, .chat-bubble-row.bot { justify-content: flex-start; }
    .chat-bubble {
        width: min(720px, 88%);
        padding: 16px 18px;
        border-radius: 18px;
        line-height: 1.6;
    }
    .chat-bubble.customer {
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: white;
        border-bottom-right-radius: 5px;
    }
    .chat-bubble.consultant {
        background: rgba(37, 99, 235, 0.08);
        color: var(--text-main);
        border-bottom-left-radius: 5px;
    }
    .chat-bubble.bot {
        background: #ecfdf5;
        color: #14532d;
        border-bottom-left-radius: 5px;
    }
    .chat-bubble-label {
        font-size: 12px;
        font-weight: 900;
        opacity: 0.8;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .chat-time {
        margin-top: 10px;
        font-size: 12px;
        opacity: 0.72;
    }
    .rating-card {
        margin-top: 22px;
        background: var(--card-bg);
        border: 1px solid var(--border-soft);
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        padding: 22px;
    }
    .rating-grid { display: grid; gap: 14px; }
    .rating-field { display: grid; gap: 7px; }
    .rating-field label { color: var(--primary-mid); font-weight: 800; }
    .rating-field select, .rating-field textarea {
        border: 1px solid var(--border-soft);
        border-radius: 14px;
        padding: 11px 12px;
        background: var(--bg-soft);
        color: var(--text-main);
    }
</style>
@endpush

@section('content')
    <section class="chat-window">
        <div class="chat-bubble-row customer">
            <div class="chat-bubble customer">
                <div class="chat-bubble-label">You</div>
                <div>{{ $chat['message'] ?? '-' }}</div>
                @if(!empty($chat['createdAt']))
                    <div class="chat-time">{{ \Carbon\Carbon::parse($chat['createdAt'])->format('d M Y, h:i A') }}</div>
                @endif
            </div>
        </div>

        <div class="chat-bubble-row {{ $botAssisted ? 'bot' : 'consultant' }}">
            <div class="chat-bubble {{ $botAssisted ? 'bot' : 'consultant' }}">
                <div class="chat-bubble-label">{{ $botAssisted ? 'BoF Virtual Assistant' : 'Bank of Fiji Consultant' }}</div>
                <div>{{ $chat['consultantReply'] ?? 'A consultant has not replied yet. Your chat is in the support queue.' }}</div>
                @if(!empty($chat['updatedAt']) && !empty($chat['consultantReply']))
                    <div class="chat-time">{{ \Carbon\Carbon::parse($chat['updatedAt'])->format('d M Y, h:i A') }}</div>
                @endif
            </div>
        </div>
    </section>

    @if($botAssisted)
        <section class="rating-card">
            <h3 style="margin-top:0; color:var(--primary-mid);">Did this answer help?</h3>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <form method="POST" action="{{ route('support-chat.resolved', $chat['documentId'] ?? $chat['id']) }}">
                    @csrf
                    @method('PATCH')
                    <button class="chat-submit-btn" type="submit">This helped</button>
                </form>
                <form method="POST" action="{{ route('support-chat.needs-consultant', $chat['documentId'] ?? $chat['id']) }}">
                    @csrf
                    @method('PATCH')
                    <button class="chat-outline-btn" type="submit">Need consultant</button>
                </form>
            </div>
        </section>
    @endif

    @if($status === 'Resolved')
        <section class="rating-card">
            @if(!empty($chat['satisfactionRating']))
                <h3 style="margin-top:0; color:var(--primary-mid);">Your Rating</h3>
                <p style="margin-bottom: 8px;"><strong>{{ $chat['satisfactionRating'] }}/5</strong></p>
                <p style="margin:0; color:var(--text-soft);">{{ $chat['satisfactionComment'] ?? 'No comment submitted.' }}</p>
            @else
                <h3 style="margin-top:0; color:var(--primary-mid);">Rate this support chat</h3>
                <form method="POST" action="{{ route('support-chat.rating', $chat['documentId'] ?? $chat['id']) }}" class="rating-grid">
                    @csrf
                    @method('PATCH')
                    <div class="rating-field">
                        <label for="satisfactionRating">Satisfaction Rating</label>
                        <select id="satisfactionRating" name="satisfactionRating" required>
                            @foreach([5, 4, 3, 2, 1] as $rating)
                                <option value="{{ $rating }}">{{ $rating }} / 5</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rating-field">
                        <label for="satisfactionComment">Comment</label>
                        <textarea id="satisfactionComment" name="satisfactionComment" rows="4" maxlength="2000">{{ old('satisfactionComment') }}</textarea>
                    </div>
                    <button class="chat-submit-btn" type="submit">Submit Rating</button>
                </form>
            @endif
        </section>
    @endif
@endsection
