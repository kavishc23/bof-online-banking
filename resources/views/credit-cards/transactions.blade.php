@extends('layouts.app')

@section('topcard')
    <h2>Credit Card Transactions</h2>
    <p>Dummy local card activity for presentation, separate from your deposit account transactions.</p>
@endsection

@section('content')
    <section style="background:var(--card-bg);border:1px solid var(--border-soft);border-radius:18px;padding:22px;box-shadow:var(--shadow-soft);">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
            <div>
                <h3 style="margin:0;color:var(--primary-mid);">{{ $card->card_type }}</h3>
                <p style="margin:6px 0;color:var(--text-soft);">{{ $card->masked_card_number }}</p>
            </div>
            <a href="{{ route('credit-cards.pay') }}" style="background:#1d4ed8;color:#fff;text-decoration:none;border-radius:12px;padding:10px 14px;font-weight:800;">Make Payment</a>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:12px;border-bottom:1px solid var(--border-soft);">Merchant</th>
                        <th style="text-align:left;padding:12px;border-bottom:1px solid var(--border-soft);">Date</th>
                        <th style="text-align:left;padding:12px;border-bottom:1px solid var(--border-soft);">Type</th>
                        <th style="text-align:left;padding:12px;border-bottom:1px solid var(--border-soft);">Status</th>
                        <th style="text-align:right;padding:12px;border-bottom:1px solid var(--border-soft);">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td style="padding:12px;border-bottom:1px solid var(--border-soft);">{{ $transaction->merchant }}</td>
                            <td style="padding:12px;border-bottom:1px solid var(--border-soft);">{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td style="padding:12px;border-bottom:1px solid var(--border-soft);">{{ $transaction->transaction_type }}</td>
                            <td style="padding:12px;border-bottom:1px solid var(--border-soft);">{{ $transaction->status }}</td>
                            <td style="padding:12px;border-bottom:1px solid var(--border-soft);text-align:right;">FJD {{ number_format((float) $transaction->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="padding:18px;">No credit card transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $transactions->links() }}</div>
    </section>
@endsection
