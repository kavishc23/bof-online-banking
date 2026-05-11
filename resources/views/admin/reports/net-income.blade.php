@extends('admin.layout')

@section('heading', 'Net Income Report')
@section('subheading', 'Generate a management PDF showing fees collected, interest paid, and net income for a selected period.')

@section('admin-content')
    {{-- CS415 report input form. Calculation is handled by NetIncomeReportService, not this Blade view. --}}
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.reports.net-income.generate') }}" class="admin-form-grid">
            @csrf

            <div class="admin-field">
                <label for="start_date">Start Date</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', now()->startOfMonth()->toDateString()) }}" required>
                @error('start_date')
                    <small style="color:#dc2626;">{{ $message }}</small>
                @enderror
            </div>

            <div class="admin-field">
                <label for="end_date">End Date</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date', now()->toDateString()) }}" required>
                @error('end_date')
                    <small style="color:#dc2626;">{{ $message }}</small>
                @enderror
            </div>

            <div class="admin-field">
                <label for="interest_days">Interest Days</label>
                <input id="interest_days" name="interest_days" type="number" min="1" value="{{ old('interest_days') }}" placeholder="Auto-calculated if blank">
                @error('interest_days')
                    <small style="color:#dc2626;">{{ $message }}</small>
                @enderror
            </div>

            <div class="admin-field">
                <label>Report Sections</label>
                <label class="switch" style="display:flex; align-items:center; gap:10px; color:var(--text-main); font-weight:700;">
                    <input type="checkbox" name="include_transaction_details" value="1" @checked(old('include_transaction_details'))>
                    Include detailed fee transactions
                </label>
                <label class="switch" style="display:flex; align-items:center; gap:10px; color:var(--text-main); font-weight:700;">
                    <input type="checkbox" name="include_account_interest_breakdown" value="1" @checked(old('include_account_interest_breakdown', true))>
                    Include account interest breakdown
                </label>
            </div>

            <div class="admin-field full">
                <label for="notes">Management Notes</label>
                <textarea id="notes" name="notes" rows="4" maxlength="500" placeholder="Optional notes for the PDF report">{{ old('notes') }}</textarea>
                @error('notes')
                    <small style="color:#dc2626;">{{ $message }}</small>
                @enderror
            </div>

            <div class="admin-actions full">
                <button type="submit" class="admin-btn">Generate PDF Report</button>
                <a href="{{ route('admin.dashboard') }}" class="admin-btn secondary">Back to Dashboard</a>
            </div>
        </form>
    </section>

    {{-- Short assignment notes shown to the admin before generating the PDF. --}}
    <section class="admin-card">
        <div class="admin-detail-grid">
            <div>
                <strong>Fee Income</strong>
                <p>Monthly account fees, Savings withdrawal charges, and other fee transactions are grouped automatically from Strapi transactions.</p>
            </div>
            <div>
                <strong>Interest Paid</strong>
                <p>Estimated interest is calculated from account balance, interest rate, and the selected report period or custom interest days.</p>
            </div>
            <div>
                <strong>Net Income</strong>
                <p>The report calculates total fees collected minus estimated interest paid for management review.</p>
            </div>
        </div>
    </section>
@endsection
