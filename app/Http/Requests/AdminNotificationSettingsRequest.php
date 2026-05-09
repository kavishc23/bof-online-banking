<?php

namespace App\Http\Requests;

use App\Services\Notifications\NotificationSettingsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminNotificationSettingsRequest extends FormRequest
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
            'settings' => [
                'required',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    foreach (array_keys((array) $value) as $eventKey) {
                        if (! in_array($eventKey, NotificationSettingsService::ALLOWED_EVENT_KEYS, true)) {
                            $fail('Invalid notification event key: '.$eventKey);
                        }
                    }
                },
            ],
            'settings.*' => 'boolean',
        ];
    }
}
