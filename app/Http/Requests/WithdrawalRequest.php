<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_id.required' => 'Please select the account to withdraw from.',
            'amount.required' => 'Please enter a withdrawal amount.',
            'amount.min' => 'Withdrawal amount must be greater than zero.',
        ];
    }
}
