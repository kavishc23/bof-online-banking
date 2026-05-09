<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BillPaymentRequest extends FormRequest
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
            'from_account_id' => 'required|integer',
            'biller_id' => 'required|integer',
            'bill_reference' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
            'is_scheduled_bill' => 'nullable',
            'scheduled_date' => 'nullable|required_with:is_scheduled_bill|date',
            'frequency' => 'nullable|required_with:is_scheduled_bill|in:Once,Daily,Weekly,Monthly',
        ];
    }
}
