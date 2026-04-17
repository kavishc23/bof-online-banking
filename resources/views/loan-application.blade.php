@extends('layouts.app')

@php
    $pageTitle = 'Loan Application - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Loan Application</h2>
    <p>Apply for a personal, home, car, or business loan through the online banking portal.</p>
@endsection

@push('styles')
<style>
    .content-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
    }

    .panel {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    body.dark-mode .panel {
        background: rgba(17,24,39,0.92);
    }

    .panel h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .panel h3,
    body.dark-mode .info-box h4,
    body.dark-mode .tips-box h4,
    body.dark-mode .documents-box h4,
    body.dark-mode .estimate-box h4 {
        color: #bfdbfe;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
    }

    body.dark-mode .section-note,
    body.dark-mode .tips-box p,
    body.dark-mode .documents-box li,
    body.dark-mode .loan-helper-text {
        color: #cbd5e1;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #1f2937;
    }

    body.dark-mode label {
        color: #e5e7eb;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 18px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        background: #fff;
        color: #111827;
    }

    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    input[type="file"] {
        padding: 10px 12px;
        background: #fff;
        cursor: pointer;
    }

    body.dark-mode input[type="file"] {
        background: #111827;
    }

    .submit-btn {
        width: 100%;
        background: #1d4ed8;
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        margin-top: 8px;
    }

    .submit-btn:hover {
        background: #1e40af;
    }

    .info-box,
    .tips-box,
    .documents-box,
    .estimate-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }

    body.dark-mode .info-box,
    body.dark-mode .tips-box,
    body.dark-mode .documents-box,
    body.dark-mode .estimate-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .info-box h4,
    .tips-box h4,
    .documents-box h4,
    .estimate-box h4 {
        margin-top: 0;
        color: #163d7a;
        margin-bottom: 12px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    body.dark-mode .summary-row {
        border-bottom-color: #334155;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row strong {
        color: #111827;
    }

    body.dark-mode .summary-row strong {
        color: #f3f4f6;
    }

    .loan-type-section {
        display: none;
        margin-top: 4px;
    }

    .loan-helper-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: -10px;
        margin-bottom: 16px;
    }

    .documents-box ul {
        margin: 0;
        padding-left: 18px;
    }

    .documents-box li {
        margin-bottom: 8px;
        color: #4b5563;
        font-size: 14px;
    }

    .documents-box li:last-child {
        margin-bottom: 0;
    }

    .muted-note {
        font-size: 12px;
        color: #6b7280;
        margin-top: 10px;
    }

    body.dark-mode .muted-note {
        color: #94a3b8;
    }

    @media (max-width: 960px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="content-grid">
    <section class="panel">
        <h3>Apply for a Loan</h3>
        <p class="section-note">Complete the form below to submit your loan request for review.</p>

        <form method="POST" action="{{ route('loan-application.submit') }}" enctype="multipart/form-data">
            @csrf

            <label for="loan_type">Loan Type</label>
            <select id="loan_type" name="loan_type" required>
                <option value="Personal" {{ old('loan_type', $selectedLoanType ?? '') == 'Personal' ? 'selected' : '' }}>Personal</option>
                <option value="Home" {{ old('loan_type', $selectedLoanType ?? '') == 'Home' ? 'selected' : '' }}>Home</option>
                <option value="Car" {{ old('loan_type', $selectedLoanType ?? '') == 'Car' ? 'selected' : '' }}>Car</option>
                <option value="Business" {{ old('loan_type', $selectedLoanType ?? '') == 'Business' ? 'selected' : '' }}>Business</option>
            </select>

            <label for="amount_requested">Amount Requested</label>
            <input
                type="number"
                step="0.01"
                min="1"
                id="amount_requested"
                name="amount_requested"
                value="{{ old('amount_requested') }}"
                placeholder="Enter requested amount"
                required
            >

            <label for="repayment_months">Repayment Period (Months)</label>
            <input
                type="number"
                min="1"
                id="repayment_months"
                name="repayment_months"
                value="{{ old('repayment_months') }}"
                placeholder="Enter repayment period"
                required
            >

            <label for="interest_rate">Estimated Interest Rate (%)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                id="interest_rate"
                name="interest_rate"
                value="{{ old('interest_rate') }}"
                placeholder="Interest rate will auto-fill"
                readonly
            >
            <div class="loan-helper-text">This estimated rate changes automatically based on the selected loan type.</div>

            <label for="employment_status">Employment Status</label>
            <select id="employment_status" name="employment_status" required>
                <option value="">-- Select Employment Status --</option>
                <option value="Employed" {{ old('employment_status') == 'Employed' ? 'selected' : '' }}>Employed</option>
                <option value="Self-Employed" {{ old('employment_status') == 'Self-Employed' ? 'selected' : '' }}>Self-Employed</option>
                <option value="Unemployed" {{ old('employment_status') == 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
            </select>

            <label for="monthly_income">Monthly Income</label>
            <input
                type="number"
                step="0.01"
                min="0"
                id="monthly_income"
                name="monthly_income"
                value="{{ old('monthly_income') }}"
                placeholder="Enter monthly income"
                required
            >

            <div id="home-loan-fields" class="loan-type-section">
                <label for="property_value">Property Value</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="property_value"
                    name="property_value"
                    value="{{ old('property_value') }}"
                    placeholder="Enter property value"
                >

                <label for="deposit_amount">Deposit Amount</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="deposit_amount"
                    name="deposit_amount"
                    value="{{ old('deposit_amount') }}"
                    placeholder="Enter deposit amount"
                >
            </div>

            <div id="car-loan-fields" class="loan-type-section">
                <label for="vehicle_details">Vehicle Make / Model</label>
                <input
                    type="text"
                    id="vehicle_details"
                    name="vehicle_details"
                    value="{{ old('vehicle_details') }}"
                    placeholder="Enter vehicle make and model"
                >

                <label for="vehicle_price">Vehicle Price</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="vehicle_price"
                    name="vehicle_price"
                    value="{{ old('vehicle_price') }}"
                    placeholder="Enter vehicle price"
                >
            </div>

            <div id="business-loan-fields" class="loan-type-section">
                <label for="business_name">Business Name</label>
                <input
                    type="text"
                    id="business_name"
                    name="business_name"
                    value="{{ old('business_name') }}"
                    placeholder="Enter business name"
                >

                <label for="business_purpose">Business Purpose</label>
                <textarea
                    id="business_purpose"
                    name="business_purpose"
                    rows="4"
                    placeholder="Describe the purpose of the business loan"
                >{{ old('business_purpose') }}</textarea>
            </div>

            <label for="loan_purpose">Purpose of Loan</label>
            <textarea
                id="loan_purpose"
                name="loan_purpose"
                rows="4"
                placeholder="Enter purpose of loan"
            >{{ old('loan_purpose') }}</textarea>

            <label for="supporting_documents">Supporting Documents</label>
            <input
                type="file"
                id="supporting_documents"
                name="supporting_documents[]"
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
            >
            <div class="loan-helper-text">
                Upload ID, payslip, bank statement, quote, invoice, or any relevant supporting documents.
            </div>

            <input type="hidden" id="estimated_monthly_repayment" name="estimated_monthly_repayment" value="{{ old('estimated_monthly_repayment') }}">
            <input type="hidden" id="estimated_total_repayment" name="estimated_total_repayment" value="{{ old('estimated_total_repayment') }}">
            <input type="hidden" id="estimated_total_interest" name="estimated_total_interest" value="{{ old('estimated_total_interest') }}">

            <button type="submit" class="submit-btn">Submit Loan Application</button>
        </form>
    </section>

    <section>
        <div class="estimate-box">
            <h4>Repayment Estimate</h4>
            <div class="summary-row">
                <span>Monthly Repayment</span>
                <strong id="monthly-repayment-display">$0.00</strong>
            </div>
            <div class="summary-row">
                <span>Total Repayment</span>
                <strong id="total-repayment-display">$0.00</strong>
            </div>
            <div class="summary-row">
                <span>Total Interest</span>
                <strong id="total-interest-display">$0.00</strong>
            </div>
        </div>

        <div class="info-box">
            <h4>Application Summary</h4>
            <div class="summary-row">
                <span>Application Status</span>
                <strong>Pending Review</strong>
            </div>
            <div class="summary-row">
                <span>Reference</span>
                <strong>Auto-generated</strong>
            </div>
            <div class="summary-row">
                <span>Submission</span>
                <strong>Online</strong>
            </div>
        </div>

        <div class="documents-box">
            <h4>Supporting Documents</h4>
            <ul>
                <li>Government-issued ID or passport</li>
                <li>Recent payslip or proof of income</li>
                <li>Recent bank statement</li>
                <li>Quote, invoice, or supporting document if applicable</li>
            </ul>
            <div class="muted-note">You can now upload supporting files directly with the loan application.</div>
        </div>

        <div class="tips-box">
            <h4>Before You Apply</h4>
            <p>Make sure your income and repayment details are accurate before submitting.</p>
            <p>Your loan request will be stored with a pending status for review.</p>
            <p>You can track submitted applications from the My Loans page.</p>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const loanType = document.getElementById('loan_type');
    const amountRequested = document.getElementById('amount_requested');
    const repaymentMonths = document.getElementById('repayment_months');
    const interestRate = document.getElementById('interest_rate');

    const homeLoanFields = document.getElementById('home-loan-fields');
    const carLoanFields = document.getElementById('car-loan-fields');
    const businessLoanFields = document.getElementById('business-loan-fields');

    const monthlyRepaymentDisplay = document.getElementById('monthly-repayment-display');
    const totalRepaymentDisplay = document.getElementById('total-repayment-display');
    const totalInterestDisplay = document.getElementById('total-interest-display');

    const estimatedMonthlyRepayment = document.getElementById('estimated_monthly_repayment');
    const estimatedTotalRepayment = document.getElementById('estimated_total_repayment');
    const estimatedTotalInterest = document.getElementById('estimated_total_interest');

    const rates = {
        Personal: 12,
        Home: 7,
        Car: 9,
        Business: 11
    };

    function toggleLoanSpecificFields() {
        const type = loanType.value;

        homeLoanFields.style.display = type === 'Home' ? 'block' : 'none';
        carLoanFields.style.display = type === 'Car' ? 'block' : 'none';
        businessLoanFields.style.display = type === 'Business' ? 'block' : 'none';

        interestRate.value = rates[type] || '';
        calculateLoanEstimate();
    }

    function calculateLoanEstimate() {
        const principal = parseFloat(amountRequested.value || 0);
        const annualRate = parseFloat(interestRate.value || 0);
        const months = parseInt(repaymentMonths.value || 0);

        let monthly = 0;
        let total = 0;
        let interest = 0;

        if (principal > 0 && months > 0) {
            const monthlyRate = annualRate / 100 / 12;

            if (monthlyRate === 0) {
                total = principal;
                monthly = total / months;
                interest = 0;
            } else {
                monthly = (principal * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -months));
                total = monthly * months;
                interest = total - principal;
            }
        }

        monthlyRepaymentDisplay.textContent = '$' + monthly.toFixed(2);
        totalRepaymentDisplay.textContent = '$' + total.toFixed(2);
        totalInterestDisplay.textContent = '$' + interest.toFixed(2);

        estimatedMonthlyRepayment.value = monthly.toFixed(2);
        estimatedTotalRepayment.value = total.toFixed(2);
        estimatedTotalInterest.value = interest.toFixed(2);
    }

    if (loanType) {
        loanType.addEventListener('change', toggleLoanSpecificFields);
        toggleLoanSpecificFields();
    }

    if (amountRequested) {
        amountRequested.addEventListener('input', calculateLoanEstimate);
    }

    if (repaymentMonths) {
        repaymentMonths.addEventListener('input', calculateLoanEstimate);
    }
</script>
@endpush