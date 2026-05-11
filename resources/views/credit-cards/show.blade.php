@extends('layouts.app')

@section('topcard')
    <h2>Credit Card Details</h2>
    <p>Review card status, repayment obligations, rewards, and security controls.</p>
@endsection

@section('content')
    <section class="cc-panel" style="background:var(--card-bg);border:1px solid var(--border-soft);border-radius:18px;padding:22px;box-shadow:var(--shadow-soft);">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0;color:var(--primary-mid);">{{ $card->card_type }}</h3>
                <p style="font-size:24px;font-weight:900;">{{ $card->masked_card_number }}</p>
                <p>{{ $card->customer_name }}</p>
            </div>
            <form method="POST" action="{{ route('credit-cards.freeze', $card) }}">
                @csrf
                @method('PATCH')
                <button class="btn" style="background:#1d4ed8;color:#fff;border:none;border-radius:12px;padding:12px 16px;font-weight:800;">
                    {{ $card->card_status === 'Frozen' ? 'Unfreeze Card' : 'Freeze Card' }}
                </button>
            </form>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-top:18px;">
            <div><strong>Status</strong><br>{{ $card->card_status }}</div>
            <div><strong>Credit Limit</strong><br>FJD {{ number_format((float) $card->credit_limit, 2) }}</div>
            <div><strong>Outstanding Balance</strong><br>FJD {{ number_format((float) $card->outstanding_balance, 2) }}</div>
            <div><strong>Available Credit</strong><br>FJD {{ number_format((float) $card->available_credit, 2) }}</div>
            <div><strong>Minimum Payment Due</strong><br>FJD {{ number_format((float) $card->minimum_payment_due, 2) }}</div>
            <div><strong>Payment Due Date</strong><br>{{ $card->payment_due_date->format('d M Y') }}</div>
            <div><strong>Reward Points</strong><br>{{ number_format($card->reward_points) }}</div>
            <div><strong>Credit Score Indicator</strong><br>{{ $creditScore }}</div>
        </div>

        <div style="margin-top:22px;">
            <strong>Utilization</strong>
            <div style="height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:8px;">
                <div style="height:100%;width:{{ min(100, $utilization) }}%;background:linear-gradient(90deg,#16a34a,#f59e0b);"></div>
            </div>
            <p style="color:var(--text-soft);">{{ number_format($utilization, 1) }}% used</p>
        </div>
    </section>
@endsection
