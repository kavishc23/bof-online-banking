@extends('layouts.app')

@php
    $pageTitle = 'My Investments - BoF Online Banking';
@endphp

@section('topcard')
    <h2>My Investments</h2>
    <p>Track your submitted investment products, projected maturity values, and current investment status.</p>
@endsection

@push('styles')
<style>
    .investment-list {
        display: grid;
        gap: 20px;
    }
    .investment-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }
    body.dark-mode .investment-card {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }
    .investment-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .investment-header h3 {
        margin: 0 0 4px;
        color: #163d7a;
        font-size: 20px;
    }
    body.dark-mode .investment-header h3,
    body.dark-mode .section-title {
        color: #bfdbfe;
    }
    .subtext {
        color: #6b7280;
        font-size: 13px;
    }
    body.dark-mode .subtext,
    body.dark-mode .meta-box,
    body.dark-mode .timeline-box {
        color: #cbd5e1;
    }
    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-processing { background: #dbeafe; color: #1d4ed8; }
    .badge-active { background: #dcfce7; color: #166534; }
    .badge-matured { background: #ede9fe; color: #6d28d9; }
    .badge-closed { background: #fee2e2; color: #991b1b; }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }
    .meta-box, .timeline-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 14px;
    }
    body.dark-mode .meta-box,
    body.dark-mode .timeline-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }
    .meta-box strong {
        display: block;
        margin-bottom: 6px;
        color: #111827;
        font-size: 13px;
    }
    body.dark-mode .meta-box strong {
        color: #f3f4f6;
    }
    .section-title {
        margin: 0 0 12px;
        font-size: 15px;
        color: #163d7a;
    }
    .timeline {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .timeline-step {
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #e5e7eb;
        color: #6b7280;
    }
    .timeline-step.active {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .timeline-step.success {
        background: #dcfce7;
        color: #166534;
    }
    .timeline-step.alt {
        background: #ede9fe;
        color: #6d28d9;
    }
    .timeline-step.danger {
        background: #fee2e2;
        color: #991b1b;
    }
    .empty-state {
        color: #6b7280;
        font-size: 15px;
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }
    body.dark-mode .empty-state {
        background: rgba(17,24,39,0.92);
        color: #cbd5e1;
    }
    @media (max-width: 960px) {
        .meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@if(!empty($investments) && count($investments) > 0)
    <div class="investment-list">
        @foreach($investments as $investment)
            @php
                $status = $investment['investmentStatus'] ?? 'Pending';
                $badgeClass = 'badge-pending';

                if ($status === 'Processing') $badgeClass = 'badge-processing';
                if ($status === 'Active') $badgeClass = 'badge-active';
                if ($status === 'Matured') $badgeClass = 'badge-matured';
                if ($status === 'Closed') $badgeClass = 'badge-closed';

                $processing = in_array($status, ['Processing', 'Active', 'Matured', 'Closed']);
                $active = in_array($status, ['Active', 'Matured', 'Closed']);
                $matured = in_array($status, ['Matured']);
                $closed = in_array($status, ['Closed']);
            @endphp

            <div class="investment-card">
                <div class="investment-header">
                    <div>
                        <h3>{{ $investment['investmentType'] ?? 'Investment' }}</h3>
                        <div class="subtext">
                            Reference: {{ $investment['referenceNumber'] ?? '-' }}
                            &nbsp;•&nbsp;
                            Submitted:
                            @if(!empty($investment['submittedAt']))
                                {{ \Carbon\Carbon::parse($investment['submittedAt'])->format('d M Y h:i A') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                </div>

                <div class="meta-grid">
                    <div class="meta-box">
                        <strong>Amount Invested</strong>
                        ${{ number_format((float)($investment['amount'] ?? 0), 2) }}
                    </div>
                    <div class="meta-box">
                        <strong>Interest Rate</strong>
                        {{ number_format((float)($investment['interestRate'] ?? 0), 2) }}%
                    </div>
                    <div class="meta-box">
                        <strong>Term</strong>
                        {{ $investment['termMonths'] ?? '-' }} months
                    </div>

                    <div class="meta-box">
                        <strong>Estimated Return</strong>
                        ${{ number_format((float)($investment['estimatedReturn'] ?? 0), 2) }}
                    </div>
                    <div class="meta-box">
                        <strong>Maturity Amount</strong>
                        ${{ number_format((float)($investment['estimatedMaturityAmount'] ?? 0), 2) }}
                    </div>
                    <div class="meta-box">
                        <strong>Funding Account</strong>
                        {{ $investment['fundingAccountNumber'] ?? '-' }}
                    </div>

                    <div class="meta-box">
                        <strong>Start Date</strong>
                        {{ !empty($investment['startDate']) ? \Carbon\Carbon::parse($investment['startDate'])->format('d M Y') : '-' }}
                    </div>
                    <div class="meta-box">
                        <strong>Maturity Date</strong>
                        {{ !empty($investment['maturityDate']) ? \Carbon\Carbon::parse($investment['maturityDate'])->format('d M Y') : '-' }}
                    </div>
                    <div class="meta-box">
                        <strong>Maturity Instruction</strong>
                        {{ $investment['maturityInstruction'] ?? '-' }}
                    </div>

                    <div class="meta-box">
                        <strong>Risk Level</strong>
                        {{ $investment['riskLevel'] ?? '-' }}
                    </div>
                    <div class="meta-box">
                        <strong>Liquidity</strong>
                        {{ $investment['liquidityType'] ?? '-' }}
                    </div>
                    <div class="meta-box">
                        <strong>Nominee</strong>
                        {{ $investment['nomineeName'] ?? 'Not provided' }}
                    </div>
                </div>

                <div class="timeline-box">
                    <h4 class="section-title">Investment Timeline</h4>
                    <div class="timeline">
                        <span class="timeline-step active">Submitted</span>
                        <span class="timeline-step {{ $processing ? 'active' : '' }}">Processing</span>
                        <span class="timeline-step {{ $active ? 'success' : '' }}">Active</span>
                        <span class="timeline-step {{ $matured ? 'alt' : '' }}">Matured</span>
                        <span class="timeline-step {{ $closed ? 'danger' : '' }}">Closed</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="empty-state">No investments submitted yet.</p>
@endif
@endsection