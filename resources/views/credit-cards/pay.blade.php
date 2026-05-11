@extends('layouts.app')

@section('topcard')
    <h2>Make Credit Card Payment</h2>
    <p>Simulate a local credit card repayment. No real payment gateway is used.</p>
@endsection

@section('content')
    <section style="display:grid;grid-template-columns:1fr 1fr;gap:22px;">
        <div style="background:var(--card-bg);border:1px solid var(--border-soft);border-radius:18px;padding:22px;box-shadow:var(--shadow-soft);">
            <h3 style="margin-top:0;color:var(--primary-mid);">Payment Details</h3>
            @if($errors->any())
                <div style="background:#fee2e2;color:#991b1b;border-radius:12px;padding:12px;margin-bottom:14px;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('credit-cards.pay.store') }}">
                @csrf
                <label style="display:block;font-weight:800;margin-bottom:8px;">Amount</label>
                <input name="amount" type="number" step="0.01" min="1" max="{{ $card->outstanding_balance }}" value="{{ old('amount', $card->minimum_payment_due) }}" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border-soft);background:var(--bg-soft);color:var(--text-main);margin-bottom:14px;">

                <label style="display:block;font-weight:800;margin-bottom:8px;">Payment Source</label>
                <select name="payment_source" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border-soft);background:var(--bg-soft);color:var(--text-main);margin-bottom:14px;">
                    <option>BoF Savings Account</option>
                    <option>BoF SimpleAccess Account</option>
                    <option>External Bank Transfer</option>
                </select>

                <label style="display:block;font-weight:800;margin-bottom:8px;">Notes</label>
                <textarea name="notes" rows="3" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border-soft);background:var(--bg-soft);color:var(--text-main);margin-bottom:14px;">{{ old('notes') }}</textarea>

                <button style="background:#1d4ed8;color:#fff;border:none;border-radius:12px;padding:12px 16px;font-weight:900;">Submit Payment</button>
            </form>
        </div>

        <div style="background:var(--card-bg);border:1px solid var(--border-soft);border-radius:18px;padding:22px;box-shadow:var(--shadow-soft);">
            <h3 style="margin-top:0;color:var(--primary-mid);">Payment Summary</h3>
            <p><strong>Card:</strong> {{ $card->masked_card_number }}</p>
            <p><strong>Outstanding Balance:</strong> FJD {{ number_format((float) $card->outstanding_balance, 2) }}</p>
            <p><strong>Minimum Due:</strong> FJD {{ number_format((float) $card->minimum_payment_due, 2) }}</p>
            <p><strong>Due Date:</strong> {{ $card->payment_due_date->format('d M Y') }}</p>
            <p style="background:#fef3c7;color:#92400e;border-radius:12px;padding:12px;">Payment Due Reminder: pay at least the minimum due before the due date to keep your card in good standing.</p>
        </div>
    </section>
@endsection
