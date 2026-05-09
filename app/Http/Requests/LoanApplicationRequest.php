<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'loan_type' => 'required|in:Personal,Home,Car,Business',
            'amount_requested' => 'required|numeric|min:1',
            'repayment_months' => 'required|integer|min:1',
            'employment_status' => 'required|in:Employed,Self-Employed,Unemployed',
            'monthly_income' => 'required|numeric|min:0',
            'loan_purpose' => 'nullable|string',
            'interest_rate' => 'nullable|numeric|min:0',
            'estimated_monthly_repayment' => 'nullable|numeric|min:0',
            'estimated_total_repayment' => 'nullable|numeric|min:0',
            'estimated_total_interest' => 'nullable|numeric|min:0',
            'property_value' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'vehicle_details' => 'nullable|string',
            'vehicle_price' => 'nullable|numeric|min:0',
            'business_name' => 'nullable|string',
            'business_purpose' => 'nullable|string',
            'supporting_documents' => 'nullable|array|max:4',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ];
    }
}
