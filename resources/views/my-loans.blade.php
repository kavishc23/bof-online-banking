@extends('layouts.app')

@php
    $pageTitle = 'My Loans - BoF Online Banking';
@endphp

@section('topcard')
    <h2>My Loan Applications</h2>
    <p>View all submitted loan applications and track their current status.</p>
@endsection

@push('styles')
<style>
    .loan-list {
        display: grid;
        gap: 20px;
    }

    .loan-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }

    body.dark-mode .loan-card {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }

    .loan-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .loan-header h3 {
        margin: 0 0 4px;
        color: #163d7a;
        font-size: 20px;
    }

    body.dark-mode .loan-header h3,
    body.dark-mode .loan-section-title {
        color: #bfdbfe;
    }

    .loan-subtext {
        color: #6b7280;
        font-size: 13px;
    }

    body.dark-mode .loan-subtext,
    body.dark-mode .loan-meta,
    body.dark-mode .review-note-text {
        color: #cbd5e1;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-review {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-approved {
        background: #dcfce7;
        color: #166534;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .loan-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .loan-meta {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 14px;
        font-size: 14px;
        color: #4b5563;
    }

    body.dark-mode .loan-meta,
    body.dark-mode .timeline-box,
    body.dark-mode .review-note-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .loan-meta strong {
        display: block;
        color: #111827;
        margin-bottom: 6px;
        font-size: 13px;
    }

    body.dark-mode .loan-meta strong {
        color: #f3f4f6;
    }

    .timeline-box,
    .review-note-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 16px;
        margin-top: 14px;
    }

    .loan-section-title {
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

    .timeline-step.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .review-note-text {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: #4b5563;
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
        .loan-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@if(!empty($loanApplications) && count($loanApplications) > 0)
    <div class="loan-list">
        @foreach($loanApplications as $loan)
            @php
                $status = $loan['applicationStatus'] ?? 'Pending';
                $badgeClass = 'badge-pending';

                if ($status === 'Under Review') $badgeClass = 'badge-review';
                if ($status === 'Approved') $badgeClass = 'badge-approved';
                if ($status === 'Rejected') $badgeClass = 'badge-rejected';

                $timelineSubmitted = true;
                $timelineReview = in_array($status, ['Under Review', 'Approved', 'Rejected']);
                $timelineDocs = in_array($status, ['Under Review', 'Approved', 'Rejected']);
                $timelineDecision = in_array($status, ['Approved', 'Rejected']);
            @endphp

            <div class="loan-card">
                <div class="loan-header">
                    <div>
                        <h3>{{ $loan['loanType'] ?? 'Loan' }} Loan</h3>
                        <div class="loan-subtext">
                            Reference: {{ $loan['referenceNumber'] ?? '-' }}
                            &nbsp;•&nbsp;
                            Submitted:
                            @if(!empty($loan['submittedAt']))
                                {{ \Carbon\Carbon::parse($loan['submittedAt'])->format('d M Y h:i A') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                </div>

                <div class="loan-grid">
                    <div class="loan-meta">
                        <strong>Amount Requested</strong>
                        ${{ number_format((float)($loan['amountRequested'] ?? 0), 2) }}
                    </div>

                    <div class="loan-meta">
                        <strong>Repayment Period</strong>
                        {{ $loan['repaymentMonths'] ?? '-' }} months
                    </div>

                    <div class="loan-meta">
                        <strong>Interest Rate</strong>
                        {{ number_format((float)($loan['interestRate'] ?? 0), 2) }}%
                    </div>

                    <div class="loan-meta">
                        <strong>Estimated Monthly Repayment</strong>
                        ${{ number_format((float)($loan['estimatedMonthlyRepayment'] ?? 0), 2) }}
                    </div>

                    <div class="loan-meta">
                        <strong>Total Repayment</strong>
                        ${{ number_format((float)($loan['estimatedTotalRepayment'] ?? 0), 2) }}
                    </div>

                    <div class="loan-meta">
                        <strong>Total Interest</strong>
                        ${{ number_format((float)($loan['estimatedTotalInterest'] ?? 0), 2) }}
                    </div>
                </div>

                <div class="loan-grid">
                    @if(!empty($loan['propertyValue']) || !empty($loan['depositAmount']))
                        <div class="loan-meta">
                            <strong>Property Value</strong>
                            ${{ number_format((float)($loan['propertyValue'] ?? 0), 2) }}
                        </div>

                        <div class="loan-meta">
                            <strong>Deposit Amount</strong>
                            ${{ number_format((float)($loan['depositAmount'] ?? 0), 2) }}
                        </div>
                    @endif

                    @if(!empty($loan['vehicleDetails']) || !empty($loan['vehiclePrice']))
                        <div class="loan-meta">
                            <strong>Vehicle Details</strong>
                            {{ $loan['vehicleDetails'] ?? '-' }}
                        </div>

                        <div class="loan-meta">
                            <strong>Vehicle Price</strong>
                            ${{ number_format((float)($loan['vehiclePrice'] ?? 0), 2) }}
                        </div>
                    @endif

                    @if(!empty($loan['businessName']) || !empty($loan['businessPurpose']))
                        <div class="loan-meta">
                            <strong>Business Name</strong>
                            {{ $loan['businessName'] ?? '-' }}
                        </div>

                        <div class="loan-meta">
                            <strong>Business Purpose</strong>
                            {{ $loan['businessPurpose'] ?? '-' }}
                        </div>
                    @endif
                </div>

                <div class="timeline-box">
                    <h4 class="loan-section-title">Application Timeline</h4>
                    <div class="timeline">
                        <span class="timeline-step {{ $timelineSubmitted ? 'active' : '' }}">Submitted</span>
                        <span class="timeline-step {{ $timelineReview ? 'active' : '' }}">Under Review</span>
                        <span class="timeline-step {{ $timelineDocs ? 'active' : '' }}">Documents Checked</span>
                        <span class="timeline-step {{ $status === 'Approved' ? 'success' : ($status === 'Rejected' ? 'danger' : ($timelineDecision ? 'active' : '')) }}">
                            Decision Made
                        </span>
                    </div>
                </div>

                <div class="review-note-box">
                    <h4 class="loan-section-title">Review Note</h4>
                    <p class="review-note-text">
                        {{ $loan['reviewNote'] ?? 'Application received and pending review by the loans team.' }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="empty-state">No loan applications submitted yet.</p>
@endif
@endsection