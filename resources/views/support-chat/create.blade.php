@extends('layouts.app')

@php
    $pageTitle = 'New Support Chat - BoF Online Banking';
@endphp

@section('topcard')
    <div class="chat-create-hero">
        <h2>New Support Chat</h2>
        <p>Describe the issue clearly so a Bank of Fiji consultant can respond.</p>
    </div>
@endsection

@push('styles')
<style>
    .chat-create-hero h2 { margin: 0 0 10px; color: var(--primary-mid); font-size: 2rem; font-weight: 800; }
    .chat-create-hero p { margin: 0; color: var(--text-soft); line-height: 1.6; }
    .chat-form-card {
        background: var(--card-bg);
        border: 1px solid var(--border-soft);
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        padding: 24px;
        display: grid;
        gap: 16px;
    }
    .chat-field { display: grid; gap: 8px; }
    .chat-field label { color: var(--primary-mid); font-weight: 800; }
    .chat-field input, .chat-field textarea {
        border: 1px solid var(--border-soft);
        border-radius: 14px;
        padding: 12px 13px;
        background: var(--bg-soft);
        color: var(--text-main);
    }
    .chat-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .chat-btn {
        border: none;
        border-radius: 12px;
        padding: 11px 15px;
        background: linear-gradient(135deg, var(--primary-light), #1e40af);
        color: white;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
    }
    .chat-btn.secondary { background: rgba(37, 99, 235, 0.08); color: var(--primary-mid); }
</style>
@endpush

@section('content')
    <form method="POST" action="{{ route('support-chat.store') }}" class="chat-form-card">
        @csrf
        <div class="chat-field">
            <label for="subject">Subject</label>
            <input id="subject" name="subject" value="{{ old('subject') }}" maxlength="255" required>
        </div>
        <div class="chat-field">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="7" maxlength="5000" required>{{ old('message') }}</textarea>
        </div>
        <div class="chat-actions">
            <button class="chat-btn" type="submit">Start Chat</button>
            <a href="{{ route('support-chat.index') }}" class="chat-btn secondary">Cancel</a>
        </div>
    </form>
@endsection

