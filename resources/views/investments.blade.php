@extends('layouts.app')

@php
    $pageTitle = 'Investments - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Investments</h2>
    <p>Choose an investment product, estimate your returns, and submit your investment request online.</p>
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
    .panel h3, .summary-box h4, .info-box h4, .tips-box h4 {
        color: #163d7a;
        margin-top: 0;
    }
    body.dark-mode .panel h3,
    body.dark-mode .summary-box h4,
    body.dark-mode .info-box h4,
    body.dark-mode .tips-box h4 {
        color: #bfdbfe;
    }
    .section-note, .helper-text, .tips-box p {
        color: #6b7280;
        font-size: 13px;
    }
    body.dark-mode .section-note,
    body.dark-mode .helper-text,
    body.dark-mode .tips-box p {
        color: #cbd5e1;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 18px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
    }
    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }
    .submit-btn {
        width: 100%;
        background: #1d4ed8;
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 12px;
        font-weight: bold;
        cursor: pointer;
    }
    .summary-box, .info-box, .tips-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }
    body.dark-mode .summary-box,
    body.dark-mode .info-box,
    body.dark-mode .tips-box {
        background: rgba(15,23,42,0.85);
        border-color: #334155;
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
        <h3>Investment Application</h3>
        <p class="section-note">Select a product, enter your investment details, and review the projected maturity value.</p>

        <form method="POST" action="{{ route('investments.submit') }}">
            @csrf

            <label for="investment_type">Investment Product</label>
            <select id="investment_type" name="investment_type" required>
                <option value="">-- Select Investment Type --</option>
                <option value="FixedDeposit" {{ old('investment_type') == 'FixedDeposit' ? 'selected' : '' }}>Fixed Deposit</option>
                <option value="GoalSavingsPlan" {{ old('investment_type') == 'GoalSavingsPlan' ? 'selected' : '' }}>Goal Savings Plan</option>
                <option value="TermInvestment" {{ old('investment_type') == 'TermInvestment' ? 'selected' : '' }}>Term Investment</option>
            </select>

            <label for="funding_account_number">Funding Account</label>
            <select id="funding_account_number" name="funding_account_number" required>
                <option value="">-- Select Funding Account --</option>
                @foreach(($customer['accounts'] ?? []) as $account)
                    <option value="{{ $account['accountNumber'] ?? '' }}" {{ old('funding_account_number') == ($account['accountNumber'] ?? '') ? 'selected' : '' }}>
                        {{ $account['accountNumber'] ?? '' }} - {{ $account['accountType'] ?? '' }} - ${{ number_format((float)($account['balance'] ?? 0), 2) }}
                    </option>
                @endforeach
            </select>

            <label for="amount">Investment Amount</label>
            <input type="number" step="0.01" min="1" id="amount" name="amount" value="{{ old('amount') }}" placeholder="Enter investment amount" required>

            <label for="term_months">Investment Term (Months)</label>
            <input type="number" min="1" id="term_months" name="term_months" value="{{ old('term_months') }}" placeholder="Enter term in months" required>

            <label for="interest_rate">Interest Rate (%)</label>
            <input type="number" step="0.01" min="0" id="interest_rate" name="interest_rate" value="{{ old('interest_rate') }}" readonly required>
            <div class="helper-text">Rate auto-fills based on selected investment product.</div>

            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>

            <label for="maturity_date">Maturity Date</label>
            <input type="date" id="maturity_date" name="maturity_date" value="{{ old('maturity_date') }}" readonly required>

            <label for="maturity_instruction">Maturity Instruction</label>
            <select id="maturity_instruction" name="maturity_instruction" required>
                <option value="">-- Select Instruction --</option>
                <option value="CreditToSourceAccount" {{ old('maturity_instruction') == 'CreditToSourceAccount' ? 'selected' : '' }}>Credit to Source Account</option>
                <option value="RenewAutomatically" {{ old('maturity_instruction') == 'RenewAutomatically' ? 'selected' : '' }}>Renew Automatically</option>
                <option value="TransferToAnotherAccount" {{ old('maturity_instruction') == 'TransferToAnotherAccount' ? 'selected' : '' }}>Transfer to Another Account</option>
            </select>

            <label for="nominee_name">Nominee Name</label>
            <input type="text" id="nominee_name" name="nominee_name" value="{{ old('nominee_name') }}" placeholder="Optional nominee name">

            <label for="nominee_relationship">Nominee Relationship</label>
            <input type="text" id="nominee_relationship" name="nominee_relationship" value="{{ old('nominee_relationship') }}" placeholder="Optional nominee relationship">

            <label for="nominee_contact">Nominee Contact</label>
            <input type="text" id="nominee_contact" name="nominee_contact" value="{{ old('nominee_contact') }}" placeholder="Optional nominee contact">

            <label for="product_description">Investment Notes</label>
            <textarea id="product_description" name="product_description" rows="4" placeholder="Optional notes about this investment">{{ old('product_description') }}</textarea>

            <input type="hidden" id="estimated_return" name="estimated_return" value="{{ old('estimated_return') }}">
            <input type="hidden" id="estimated_maturity_amount" name="estimated_maturity_amount" value="{{ old('estimated_maturity_amount') }}">
            <input type="hidden" id="risk_level" name="risk_level" value="{{ old('risk_level') }}">
            <input type="hidden" id="liquidity_type" name="liquidity_type" value="{{ old('liquidity_type') }}">

            <button type="submit" class="submit-btn">Submit Investment</button>
        </form>
    </section>

    <section>
        <div class="summary-box">
            <h4>Investment Projection</h4>
            <div class="summary-row">
                <span>Estimated Return</span>
                <strong id="estimated-return-display">$0.00</strong>
            </div>
            <div class="summary-row">
                <span>Maturity Amount</span>
                <strong id="maturity-amount-display">$0.00</strong>
            </div>
            <div class="summary-row">
                <span>Maturity Date</span>
                <strong id="maturity-date-display">-</strong>
            </div>
        </div>

        <div class="info-box">
            <h4>Product Details</h4>
            <div class="summary-row">
                <span>Risk Level</span>
                <strong id="risk-level-display">-</strong>
            </div>
            <div class="summary-row">
                <span>Liquidity</span>
                <strong id="liquidity-type-display">-</strong>
            </div>
            <div class="summary-row">
                <span>Status on Submit</span>
                <strong>Pending</strong>
            </div>
        </div>

        <div class="tips-box">
            <h4>Investment Notes</h4>
            <p>Fixed Deposit usually offers higher returns with locked access until maturity.</p>
            <p>Goal Savings Plan is more flexible and suited for planned savings goals.</p>
            <p>Term Investment is designed for medium-term growth with a fixed maturity instruction.</p>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const investmentType = document.getElementById('investment_type');
    const amountInput = document.getElementById('amount');
    const termMonthsInput = document.getElementById('term_months');
    const interestRateInput = document.getElementById('interest_rate');
    const startDateInput = document.getElementById('start_date');
    const maturityDateInput = document.getElementById('maturity_date');

    const estimatedReturnDisplay = document.getElementById('estimated-return-display');
    const maturityAmountDisplay = document.getElementById('maturity-amount-display');
    const maturityDateDisplay = document.getElementById('maturity-date-display');
    const riskLevelDisplay = document.getElementById('risk-level-display');
    const liquidityTypeDisplay = document.getElementById('liquidity-type-display');

    const estimatedReturnInput = document.getElementById('estimated_return');
    const estimatedMaturityAmountInput = document.getElementById('estimated_maturity_amount');
    const riskLevelInput = document.getElementById('risk_level');
    const liquidityTypeInput = document.getElementById('liquidity_type');

    const productRules = {
        FixedDeposit: {
            rate: 6.50,
            risk: 'Low',
            liquidity: 'Locked'
        },
        GoalSavingsPlan: {
            rate: 4.00,
            risk: 'Low',
            liquidity: 'Flexible'
        },
        TermInvestment: {
            rate: 5.25,
            risk: 'Moderate',
            liquidity: 'Locked'
        }
    };

    function addMonthsToDate(dateString, months) {
        if (!dateString || !months) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';

        date.setMonth(date.getMonth() + months);
        return date.toISOString().split('T')[0];
    }

    function updateInvestmentDetails() {
        const type = investmentType.value;
        const amount = parseFloat(amountInput.value || 0);
        const termMonths = parseInt(termMonthsInput.value || 0);
        const startDate = startDateInput.value;

        const selected = productRules[type] || null;

        if (!selected) {
            interestRateInput.value = '';
            riskLevelInput.value = '';
            liquidityTypeInput.value = '';
            riskLevelDisplay.textContent = '-';
            liquidityTypeDisplay.textContent = '-';
            maturityDateInput.value = '';
            maturityDateDisplay.textContent = '-';
            estimatedReturnDisplay.textContent = '$0.00';
            maturityAmountDisplay.textContent = '$0.00';
            estimatedReturnInput.value = '';
            estimatedMaturityAmountInput.value = '';
            return;
        }

        interestRateInput.value = selected.rate.toFixed(2);
        riskLevelInput.value = selected.risk;
        liquidityTypeInput.value = selected.liquidity;
        riskLevelDisplay.textContent = selected.risk;
        liquidityTypeDisplay.textContent = selected.liquidity;

        const maturityDate = addMonthsToDate(startDate, termMonths);
        maturityDateInput.value = maturityDate;
        maturityDateDisplay.textContent = maturityDate || '-';

        let estimatedReturn = 0;
        let maturityAmount = 0;

        if (amount > 0 && termMonths > 0) {
            estimatedReturn = amount * (selected.rate / 100) * (termMonths / 12);
            maturityAmount = amount + estimatedReturn;
        }

        estimatedReturnDisplay.textContent = '$' + estimatedReturn.toFixed(2);
        maturityAmountDisplay.textContent = '$' + maturityAmount.toFixed(2);
        estimatedReturnInput.value = estimatedReturn.toFixed(2);
        estimatedMaturityAmountInput.value = maturityAmount.toFixed(2);
    }

    if (investmentType) investmentType.addEventListener('change', updateInvestmentDetails);
    if (amountInput) amountInput.addEventListener('input', updateInvestmentDetails);
    if (termMonthsInput) termMonthsInput.addEventListener('input', updateInvestmentDetails);
    if (startDateInput) startDateInput.addEventListener('change', updateInvestmentDetails);

    updateInvestmentDetails();
</script>
@endpush