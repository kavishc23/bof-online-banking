<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
            'transfer_mode' => 'required|in:internal,local_bank',
            'from_account_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'to_account_number' => 'nullable|string|required_if:transfer_mode,internal',
            'destination_institution_id' => 'nullable|integer|required_if:transfer_mode,local_bank',
            'destination_account_number' => 'nullable|string|required_if:transfer_mode,local_bank',
            'beneficiary_name' => 'nullable|string|required_if:transfer_mode,local_bank',
            'is_scheduled_transfer' => 'nullable',
            'scheduled_date' => 'nullable|required_with:is_scheduled_transfer|date',
            'frequency' => 'nullable|required_with:is_scheduled_transfer|in:Once,Daily,Weekly,Monthly',
        ];
    }
}
