@extends('admin.layout')

@section('heading', 'Edit Account')
@section('subheading', 'Update account type, balance, maintenance fee, and interest rate.')

@section('admin-content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.accounts.update', $account['documentId'] ?? $account['id']) }}" class="admin-form-grid">
            @csrf
            @method('PATCH')
            @include('admin.accounts.partials.form', ['account' => $account, 'isCreate' => false])
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Save Account</button>
                <a class="admin-btn secondary" href="{{ route('admin.accounts.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

