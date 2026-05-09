<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InvestmentRequest extends FormRequest
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
            'investment_type' => 'required|in:FixedDeposit,GoalSavingsPlan,TermInvestment',
            'funding_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'estimated_return' => 'nullable|numeric|min:0',
            'estimated_maturity_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'maturity_date' => 'required|date',
            'maturity_instruction' => 'required|in:CreditToSourceAccount,RenewAutomatically,TransferToAnotherAccount',
            'risk_level' => 'required|in:Low,Moderate',
            'liquidity_type' => 'required|in:Locked,Flexible',
            'nominee_name' => 'nullable|string',
            'nominee_relationship' => 'nullable|string',
            'nominee_contact' => 'nullable|string',
            'product_description' => 'nullable|string',
        ];
    }
}
