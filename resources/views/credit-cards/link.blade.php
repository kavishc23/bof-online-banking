@extends('layouts.app')

@section('topcard')
    <h2>Link Credit Card</h2>
    <p>Simulate linking an existing Bank of Fiji credit card to online banking.</p>
@endsection

@section('content')
    <section style="background:var(--card-bg);border:1px solid var(--border-soft);border-radius:18px;padding:22px;box-shadow:var(--shadow-soft);max-width:760px;">
        <h3 style="margin-top:0;color:var(--primary-mid);">Card Verification</h3>
        @if($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border-radius:12px;padding:12px;margin-bottom:14px;">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('credit-cards.link.store') }}">
            @csrf
            <label style="display:block;font-weight:800;margin-bottom:8px;">Card Reference Number</label>
            <input name="card_reference_number" value="{{ old('card_reference_number', 'BOF-CC-4821') }}" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border-soft);background:var(--bg-soft);color:var(--text-main);margin-bottom:14px;">

            <label style="display:block;font-weight:800;margin-bottom:8px;">Last 4 Digits</label>
            <input name="last_four_digits" maxlength="4" value="{{ old('last_four_digits', '4821') }}" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border-soft);background:var(--bg-soft);color:var(--text-main);margin-bottom:14px;">

            <label style="display:block;font-weight:800;margin-bottom:8px;">Cardholder Name</label>
            <input name="cardholder_name" value="{{ old('cardholder_name', session('customer.firstName', 'Kavish').' '.session('customer.lastName', 'Chandra')) }}" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border-soft);background:var(--bg-soft);color:var(--text-main);margin-bottom:14px;">

            <button style="background:#1d4ed8;color:#fff;border:none;border-radius:12px;padding:12px 16px;font-weight:900;">Link Credit Card</button>
        </form>
    </section>
@endsection
