@extends('layouts.app')

@php
    $pageTitle = 'Live Chat Support - BoF Online Banking';
@endphp

@section('topcard')
    <div class="chat-hero">
        <div>
            <div class="chat-eyebrow">Customer Support</div>
            <h2>Live Chat Support</h2>
            <p>Start a support conversation and follow consultant replies in a simple chat-style workspace.</p>
        </div>
        <a href="{{ route('support-chat.create') }}" class="chat-primary-btn">New Support Chat</a>
    </div>
@endsection

@push('styles')
<style>
    .chat-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        position: relative;
        z-index: 1;
    }
    .chat-eyebrow {
        display: inline-block;
        margin-bottom: 10px;
        color: var(--primary-light);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .chat-hero h2 { margin: 0 0 10px; color: var(--primary-mid); font-size: 2rem; font-weight: 800; }
    .chat-hero p { margin: 0; color: var(--text-soft); line-height: 1.6; }
    .chat-primary-btn, .chat-open-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 15px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        border: none;
    }
    .chat-list { display: grid; gap: 16px; }
    .chat-card {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 18px;
        align-items: center;
        padding: 20px;
        border-radius: 18px;
        background: var(--card-bg);
        border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-soft);
    }
    .chat-card h3 { margin: 0 0 8px; color: var(--text-main); font-size: 1.1rem; }
    .chat-meta { color: var(--text-soft); font-size: 0.9rem; line-height: 1.55; }
    .chat-preview { margin-top: 10px; color: var(--text-soft); }
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
    @media (max-width: 760px) {
        .chat-hero, .chat-card { grid-template-columns: 1fr; flex-direction: column; }
    }
</style>
@endpush

@section('content')
    <div class="chat-list">
        @forelse($chats as $chat)
            @php
                $status = $chat['ticketStatus'] ?? 'Open';
                $statusClass = match ($status) {
                    'Resolved' => 'status-resolved',
                    'Unresolved' => 'status-unresolved',
                    'InProgress' => 'status-inprogress',
                    default => 'status-open',
                };
            @endphp
            <article class="chat-card">
                <div>
                    <div class="chat-meta">
                        {{ $chat['ticketNumber'] ?? 'Support Chat' }}
                        @if(!empty($chat['createdAt']))
                            · {{ \Carbon\Carbon::parse($chat['createdAt'])->format('d M Y, h:i A') }}
                        @endif
                    </div>
                    <h3>{{ $chat['subject'] ?? 'Support conversation' }}</h3>
                    <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                    <div class="chat-preview">
                        {{ \Illuminate\Support\Str::limit($chat['consultantReply'] ?? $chat['message'] ?? 'No messages yet.', 130) }}
                    </div>
                </div>
                <a class="chat-open-btn" href="{{ route('support-chat.show', $chat['documentId'] ?? $chat['id']) }}">Open Chat</a>
            </article>
        @empty
            <section class="chat-card">
                <div>
                    <h3>No support conversations yet.</h3>
                    <div class="chat-meta">Start a chat when you need help with online banking.</div>
                </div>
                <a class="chat-open-btn" href="{{ route('support-chat.create') }}">New Support Chat</a>
            </section>
        @endforelse
    </div>
@endsection

