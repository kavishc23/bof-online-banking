@extends('layouts.app')

@php
    $pageTitle = 'Tax Report - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Annual Tax Report</h2>
    <p>View yearly interest earnings, withholding tax applied, and FRCS reporting details for your banking profile.</p>
@endsection

@push('styles')
<style>
    .tax-grid {
        display: grid;
        grid-template-columns: 1.45fr 1fr;
        gap: 24px;
    }

    .panel,
    .card-box {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.07);
        border: 1px solid #e5e7eb;
    }

    body.dark-mode .panel,
    body.dark-mode .card-box,
    body.dark-mode table {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }

    .panel h3,
    .card-box h4 {
        margin-top: 0;
        margin-bottom: 14px;
        color: #163d7a;
    }

    body.dark-mode .panel h3,
    body.dark-mode .card-box h4 {
        color: #bfdbfe;
    }

    .section-note,
    .muted-text,
    .notice-text {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.65;
    }

    body.dark-mode .section-note,
    body.dark-mode .muted-text,
    body.dark-mode .notice-text {
        color: #cbd5e1;
    }

    .year-filter {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 14px;
        align-items: end;
        margin-bottom: 22px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #1f2937;
    }

    body.dark-mode label {
        color: #e5e7eb;
    }

    select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        background: #fff;
        color: #111827;
    }

    body.dark-mode select {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    .filter-btn {
        background: #1d4ed8;
        color: white;
        border: none;
        padding: 12px 18px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        min-width: 120px;
    }

    .filter-btn:hover {
        background: #1e40af;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .summary-card {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 18px;
    }

    body.dark-mode .summary-card {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .summary-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 800;
    }

    body.dark-mode .summary-label {
        color: #cbd5e1;
    }

    .summary-value {
        font-size: 26px;
        font-weight: 800;
        color: #163d7a;
    }

    body.dark-mode .summary-value {
        color: #bfdbfe;
    }

    .summary-sub {
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    body.dark-mode .summary-sub {
        color: #cbd5e1;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 11px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    body.dark-mode .detail-row {
        border-bottom-color: #334155;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-row strong {
        color: #111827;
        text-align: right;
    }

    body.dark-mode .detail-row strong {
        color: #f3f4f6;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        display: inline-block;
    }

    .badge-valid {
        background: #dcfce7;
        color: #166534;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .alert-box {
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        font-size: 14px;
        line-height: 1.6;
    }

    .alert-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .alert-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .alert-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    body.dark-mode .alert-info {
        background: rgba(29, 78, 216, 0.14);
        border-color: rgba(96, 165, 250, 0.32);
        color: #bfdbfe;
    }

    body.dark-mode .alert-warning {
        background: rgba(146, 64, 14, 0.16);
        border-color: rgba(251, 191, 36, 0.28);
        color: #fde68a;
    }

    body.dark-mode .alert-danger {
        background: rgba(153, 27, 27, 0.16);
        border-color: rgba(248, 113, 113, 0.25);
        color: #fecaca;
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 12px;
        overflow: hidden;
    }

    th, td {
        padding: 14px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        font-size: 14px;
    }

    body.dark-mode th,
    body.dark-mode td {
        border-bottom-color: #334155;
    }

    th {
        background: #eaf2ff;
        color: #163d7a;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    body.dark-mode th {
        background: #172338;
        color: #bfdbfe;
    }

    tr:hover {
        background: #f9fbff;
    }

    body.dark-mode tr:hover {
        background: rgba(30,41,59,0.65);
    }

    .empty-state {
        color: #6b7280;
        font-size: 15px;
    }

    body.dark-mode .empty-state {
        color: #cbd5e1;
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 960px) {
        .tax-grid,
        .summary-grid,
        .details-grid,
        .year-filter {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $selectedYear = request('year', now()->year);

    $grossInterest = (float)($grossInterest ?? ($taxReport['grossInterest'] ?? 0));
    $withholdingRate = (float)($withholdingRate ?? ($taxReport['withholdingTaxRate'] ?? 0));
    $withholdingTaxAmount = (float)($withholdingTaxAmount ?? ($taxReport['withholdingTaxAmount'] ?? 0));
    $netInterest = (float)($netInterest ?? ($taxReport['netInterest'] ?? 0));

    $tin = $customer['tin'] ?? '';
    $residencyStatus = $customer['residencyStatus'] ?? 'Resident';
    $taxProfileStatus = $customer['taxProfileStatus'] ?? ((empty($tin) || $residencyStatus === 'NonResident') ? ($residencyStatus === 'NonResident' ? 'NonResident' : 'MissingTIN') : 'ValidTIN');

    $frcsStatus = $taxReport['frcsSubmissionStatus'] ?? 'Pending';
    $adjustmentStatus = $taxReport['adjustmentStatus'] ?? 'None';

    $taxBadge = 'badge-warning';
    if ($taxProfileStatus === 'ValidTIN') $taxBadge = 'badge-valid';
    if ($taxProfileStatus === 'NonResident') $taxBadge = 'badge-danger';

    $frcsBadge = 'badge-warning';
    if ($frcsStatus === 'Submitted') $frcsBadge = 'badge-valid';
    if ($frcsStatus === 'Resubmitted') $frcsBadge = 'badge-info';

    $adjustBadge = $adjustmentStatus === 'Adjusted' ? 'badge-info' : 'badge-warning';

    $taxExplanation = 'Withholding tax has not been applied because a valid TIN is recorded and the customer is classified as resident.';
    if ($taxProfileStatus === 'MissingTIN') {
        $taxExplanation = 'Withholding tax has been applied because no valid TIN is currently recorded for this customer.';
    } elseif ($taxProfileStatus === 'NonResident') {
        $taxExplanation = 'Withholding tax has been applied because the customer is classified as non-resident.';
    }
@endphp

<div class="tax-grid">
    <section class="panel">
        <h3>Year-End Interest & Tax Summary</h3>
        <p class="section-note">
            This statement summarizes interest credited to your banking relationship for the selected year and any withholding tax applied before reporting to FRCS.
        </p>

        <form method="GET" action="{{ route('tax-report') }}">
            <div class="year-filter">
                <div>
                    <label for="year">Reporting Year</label>
                    <select name="year" id="year">
                        @for($year = now()->year; $year >= now()->year - 5; $year--)
                            <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="filter-btn">Load Report</button>
            </div>
        </form>

        @if($taxProfileStatus === 'MissingTIN')
            <div class="alert-box alert-warning">
                A valid TIN is not currently recorded for this customer. Under the bank’s tax handling rules, withholding tax may be deducted on reportable interest until a TIN is provided and the profile is updated.
            </div>
        @elseif($taxProfileStatus === 'NonResident')
            <div class="alert-box alert-danger">
                This customer is currently classified as non-resident. Non-resident withholding tax has been applied to reportable interest for the selected reporting year.
            </div>
        @else
            <div class="alert-box alert-info">
                Your tax profile is in good standing for reporting. Interest has been assessed using the resident profile recorded by the bank.
            </div>
        @endif

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Gross Interest</div>
                <div class="summary-value">${{ number_format($grossInterest, 2) }}</div>
                <div class="summary-sub">Total reportable interest earned</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Tax Rate Applied</div>
                <div class="summary-value">{{ number_format($withholdingRate, 2) }}%</div>
                <div class="summary-sub">Annual withholding tax rate</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Tax Deducted</div>
                <div class="summary-value">${{ number_format($withholdingTaxAmount, 2) }}</div>
                <div class="summary-sub">Amount withheld before reporting</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Net Interest</div>
                <div class="summary-value">${{ number_format($netInterest, 2) }}</div>
                <div class="summary-sub">Interest remaining after deductions</div>
            </div>
        </div>

        <div class="details-grid">
            <div class="card-box">
                <h4>Report Details</h4>
                <div class="detail-row">
                    <span>Reference Number</span>
                    <strong>{{ $taxReport['referenceNumber'] ?? '-' }}</strong>
                </div>
                <div class="detail-row">
                    <span>Reporting Year</span>
                    <strong>{{ $taxReport['reportingYear'] ?? $selectedYear }}</strong>
                </div>
                <div class="detail-row">
                    <span>Customer Name</span>
                    <strong>{{ $taxReport['customerName'] ?? trim(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) }}</strong>
                </div>
                <div class="detail-row">
                    <span>Residency Status</span>
                    <strong>{{ $residencyStatus }}</strong>
                </div>
                <div class="detail-row">
                    <span>TIN Used For Reporting</span>
                    <strong>{{ !empty($taxReport['tinNumber'] ?? $tin) ? ($taxReport['tinNumber'] ?? $tin) : 'Not Provided' }}</strong>
                </div>
                <div class="detail-row">
                    <span>Generated At</span>
                    <strong>
                        @if(!empty($taxReport['generatedAt']))
                            {{ \Carbon\Carbon::parse($taxReport['generatedAt'])->format('d M Y h:i A') }}
                        @else
                            -
                        @endif
                    </strong>
                </div>
            </div>

            <div class="card-box">
                <h4>FRCS Reporting Status</h4>
                <div class="detail-row">
                    <span>Submission Status</span>
                    <strong><span class="badge {{ $frcsBadge }}">{{ $frcsStatus }}</span></strong>
                </div>
                <div class="detail-row">
                    <span>Adjustment Status</span>
                    <strong><span class="badge {{ $adjustBadge }}">{{ $adjustmentStatus }}</span></strong>
                </div>
                <div class="detail-row">
                    <span>Previous Tax Amount</span>
                    <strong>${{ number_format((float)($taxReport['previousTaxAmount'] ?? 0), 2) }}</strong>
                </div>
                <div class="detail-row">
                    <span>Revised Tax Amount</span>
                    <strong>${{ number_format((float)($taxReport['revisedTaxAmount'] ?? 0), 2) }}</strong>
                </div>
                <div class="detail-row">
                    <span>Adjustment Reason</span>
                    <strong>{{ $taxReport['adjustmentReason'] ?? 'No adjustment recorded' }}</strong>
                </div>
                <div class="detail-row">
                    <span>Last Updated</span>
                    <strong>
                        @if(!empty($taxReport['lastUpdatedAt']))
                            {{ \Carbon\Carbon::parse($taxReport['lastUpdatedAt'])->format('d M Y h:i A') }}
                        @else
                            -
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        <div class="card-box">
            <h4>Tax Treatment Explanation</h4>
            <p class="notice-text">{{ $taxExplanation }}</p>
            <p class="notice-text">
                Where customer tax information changes after year-end, the bank may recalculate the applicable withholding amount and submit an adjusted report to FRCS.
            </p>
        </div>

        <div class="card-box">
            <h4>Tax Report History</h4>

            @if(!empty($taxReports) && count($taxReports) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Gross Interest</th>
                                <th>Tax Deducted</th>
                                <th>Net Interest</th>
                                <th>FRCS Status</th>
                                <th>Adjustment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taxReports as $report)
                                <tr>
                                    <td>{{ $report['reportingYear'] ?? '-' }}</td>
                                    <td>${{ number_format((float)($report['grossInterest'] ?? 0), 2) }}</td>
                                    <td>${{ number_format((float)($report['withholdingTaxAmount'] ?? 0), 2) }}</td>
                                    <td>${{ number_format((float)($report['netInterest'] ?? 0), 2) }}</td>
                                    <td>{{ $report['frcsSubmissionStatus'] ?? 'Pending' }}</td>
                                    <td>{{ $report['adjustmentStatus'] ?? 'None' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty-state">No tax reports available yet.</p>
            @endif
        </div>
    </section>

    <section>
        <div class="card-box">
            <h4>Customer Tax Profile</h4>
            <div class="detail-row">
                <span>TIN</span>
                <strong>{{ !empty($tin) ? $tin : 'Not Provided' }}</strong>
            </div>
            <div class="detail-row">
                <span>Residency Status</span>
                <strong>{{ $residencyStatus }}</strong>
            </div>
            <div class="detail-row">
                <span>Tax Profile Status</span>
                <strong><span class="badge {{ $taxBadge }}">{{ $taxProfileStatus }}</span></strong>
            </div>
            <div class="detail-row">
                <span>Current Withholding Rate</span>
                <strong>{{ number_format((float)($customer['withholdingTaxRate'] ?? $withholdingRate), 2) }}%</strong>
            </div>
        </div>

        <div class="card-box">
            <h4>FRCS Transmission Overview</h4>
            <p class="notice-text">
                At the end of each reporting year, the bank prepares an annual interest summary and submits the tax reporting record to FRCS through the linked reporting channel.
            </p>
            <p class="notice-text">
                This page reflects the bank’s recorded reporting status, including original submission, adjustment, and resubmission where applicable.
            </p>
        </div>

        <div class="card-box">
            <h4>Adjustment Rules</h4>
            <p class="notice-text">
                An adjustment may be made when:
            </p>
            <ul class="notice-text" style="padding-left: 18px; margin-top: 8px;">
                <li>a valid TIN is later provided,</li>
                <li>the customer’s residency status changes, or</li>
                <li>the originally reported withholding tax requires correction.</li>
            </ul>
        </div>

        <div class="card-box">
            <h4>Important Notice</h4>
            <p class="notice-text">
                This report is a banking tax summary prepared for reporting support purposes. It is not a substitute for formal tax advice or a final personal tax assessment.
            </p>
        </div>
    </section>
</div>
@endsection