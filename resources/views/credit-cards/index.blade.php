@extends('layouts.app')

@php($pageTitle = 'Credit Cards - BoF Online Banking')

@section('topcard')
    <h2>Credit Cards</h2>
    <p>Manage your linked Bank of Fiji credit cards, repayments, rewards, and recent card activity.</p>
@endsection

@push('styles')
<style>
    .cc-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 22px; }
    .cc-card, .cc-panel { background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: 18px; padding: 22px; box-shadow: var(--shadow-soft); }
    .cc-hero { min-height: 230px; color: #fff; background: linear-gradient(135deg, #101827, #1d4ed8 58%, #0f766e); border-radius: 20px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 16px 30px rgba(15, 23, 42, .22); }
    .cc-chip { width: 42px; height: 30px; border-radius: 8px; background: linear-gradient(135deg, #fde68a, #f59e0b); }
    .cc-number { font-size: 24px; letter-spacing: 2px; font-weight: 800; }
    .cc-row { display: flex; justify-content: space-between; gap: 16px; align-items: center; }
    .cc-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .cc-btn { display: inline-flex; align-items: center; justify-content: center; border: none; border-radius: 12px; padding: 10px 14px; background: #1d4ed8; color: #fff; text-decoration: none; font-weight: 800; cursor: pointer; }
    .cc-btn.secondary { background: rgba(29, 78, 216, .09); color: var(--primary-mid); }
    .cc-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin: 22px 0; }
    .cc-stat { background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: 16px; padding: 18px; }
    .cc-stat span { color: var(--text-soft); font-size: 12px; text-transform: uppercase; font-weight: 900; }
    .cc-stat strong { display: block; margin-top: 8px; color: var(--primary-mid); font-size: 22px; }
    .cc-progress { height: 10px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .cc-progress div { height: 100%; background: linear-gradient(90deg, #16a34a, #f59e0b); }
    .cc-badge { border-radius: 999px; padding: 6px 10px; font-weight: 900; font-size: 12px; background: #dcfce7; color: #166534; }
    .cc-badge.warn { background: #fef3c7; color: #92400e; }
    .cc-badge.frozen { background: #fee2e2; color: #991b1b; }
    .cc-table { width: 100%; border-collapse: collapse; }
    .cc-table th, .cc-table td { padding: 12px 10px; border-bottom: 1px solid var(--border-soft); text-align: left; }
    .cc-table th { color: var(--text-soft); font-size: 12px; text-transform: uppercase; }
    @media (max-width: 980px) { .cc-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    <div class="cc-grid">
        <section class="cc-card">
            <div class="cc-hero">
                <div class="cc-row">
                    <div>
                        <div style="font-weight:900;">Bank of Fiji</div>
                        <div>{{ $card->card_type }}</div>
                    </div>
                    <div class="cc-chip"></div>
                </div>
                <div>
                    <div class="cc-number">{{ $card->masked_card_number }}</div>
                    <div class="cc-row" style="margin-top:20px;">
                        <span>{{ strtoupper($card->customer_name) }}</span>
                        <span>{{ $card->payment_due_date->format('m/y') }}</span>
                    </div>
                </div>
            </div>
            <div class="cc-actions">
                <a class="cc-btn" href="{{ route('credit-cards.pay') }}">Make Payment</a>
                <a class="cc-btn secondary" href="{{ route('credit-cards.transactions') }}">Transactions</a>
                <a class="cc-btn secondary" href="{{ route('credit-cards.show', $card) }}">Card Details</a>
                <a class="cc-btn secondary" href="{{ route('credit-cards.link') }}">Link Card</a>
            </div>
        </section>

        <section class="cc-panel">
            <div class="cc-row">
                <h3 style="margin:0;color:var(--primary-mid);">Card Health</h3>
                <span class="cc-badge {{ $card->card_status === 'Frozen' ? 'frozen' : '' }}">{{ $card->card_status }}</span>
            </div>
            <p style="color:var(--text-soft);">Utilization is {{ number_format($utilization, 1) }}% of your approved credit limit.</p>
            <div class="cc-progress"><div style="width: {{ min(100, $utilization) }}%;"></div></div>
            <div class="cc-row" style="margin-top:18px;">
                <div>
                    <span style="color:var(--text-soft);font-weight:800;">Credit Score Indicator</span>
                    <strong style="display:block;font-size:28px;color:var(--primary-mid);">{{ $creditScore }}</strong>
                </div>
                <div>
                    <span style="color:var(--text-soft);font-weight:800;">Rewards Progress</span>
                    <strong style="display:block;font-size:28px;color:var(--primary-mid);">{{ number_format($card->reward_points) }}/{{ number_format($rewardGoal) }}</strong>
                </div>
            </div>
            @if($utilization >= 80)
                <p class="cc-badge warn" style="display:inline-block;margin-top:16px;">Credit Limit Warning</p>
            @else
                <p class="cc-badge" style="display:inline-block;margin-top:16px;">Available credit is healthy</p>
            @endif
        </section>
    </div>

    <div class="cc-stats">
        <div class="cc-stat"><span>Credit Limit</span><strong>FJD {{ number_format((float) $card->credit_limit, 2) }}</strong></div>
        <div class="cc-stat"><span>Available Credit</span><strong>FJD {{ number_format((float) $card->available_credit, 2) }}</strong></div>
        <div class="cc-stat"><span>Outstanding Balance</span><strong>FJD {{ number_format((float) $card->outstanding_balance, 2) }}</strong></div>
        <div class="cc-stat"><span>Minimum Due</span><strong>FJD {{ number_format((float) $card->minimum_payment_due, 2) }}</strong></div>
        <div class="cc-stat"><span>Payment Due Date</span><strong>{{ $card->payment_due_date->format('d M Y') }}</strong></div>
        <div class="cc-stat"><span>Reward Points</span><strong>{{ number_format($card->reward_points) }}</strong></div>
    </div>

    <section class="cc-panel">
        <div class="cc-row">
            <h3 style="margin:0;color:var(--primary-mid);">Recent Card Activity</h3>
            <a class="cc-btn secondary" href="{{ route('credit-cards.transactions') }}">View All</a>
        </div>
        <table class="cc-table">
            <thead><tr><th>Merchant</th><th>Date</th><th>Type</th><th>Status</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach($recentTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->merchant }}</td>
                        <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td>{{ $transaction->transaction_type }}</td>
                        <td>{{ $transaction->status }}</td>
                        <td>FJD {{ number_format((float) $transaction->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
