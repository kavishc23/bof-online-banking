<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BeneficiaryRequest extends FormRequest
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
            'nickname' => 'required|string|max:100',
            'beneficiary_name' => 'required|string|max:100',
            'transfer_mode' => 'required|in:Internal,LocalBank',
            'institution_name' => 'nullable|string|max:100',
            'account_number' => 'required|string|max:100',
        ];
    }
}
