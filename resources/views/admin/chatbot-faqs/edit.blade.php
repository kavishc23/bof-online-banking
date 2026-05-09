@extends('admin.layout')

@section('heading', 'Edit Chatbot FAQ')
@section('subheading', 'Update chatbot keywords, answer, category, or activation.')

@section('admin-content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.chatbot-faqs.update', $faq['documentId'] ?? $faq['id']) }}" class="admin-form-grid">
            @csrf
            @method('PATCH')
            @include('admin.chatbot-faqs.partials.form', ['faq' => $faq])
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Save FAQ</button>
                <a class="admin-btn secondary" href="{{ route('admin.chatbot-faqs.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

