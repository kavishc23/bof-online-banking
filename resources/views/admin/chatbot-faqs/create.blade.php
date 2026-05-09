@extends('admin.layout')

@section('heading', 'Create Chatbot FAQ')
@section('subheading', 'Add a keyword-triggered answer for the BoF Virtual Assistant.')

@section('admin-content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.chatbot-faqs.store') }}" class="admin-form-grid">
            @csrf
            @include('admin.chatbot-faqs.partials.form', ['faq' => null])
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Create FAQ</button>
                <a class="admin-btn secondary" href="{{ route('admin.chatbot-faqs.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

