@extends('admin.layout')

@section('heading', 'Create Account')
@section('subheading', 'Create a new account through the Strapi backend.')

@section('admin-content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.accounts.store') }}" class="admin-form-grid">
            @csrf
            @include('admin.accounts.partials.form', ['account' => null, 'isCreate' => true])
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Create Account</button>
                <a class="admin-btn secondary" href="{{ route('admin.accounts.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

