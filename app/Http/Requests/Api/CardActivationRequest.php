<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CardActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'card_number' => ['required', 'string', 'max:50'],
            'national_id' => ['required', 'string', 'min:5', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.required' => __('Card number is required.'),
            'card_number.max'      => __('Card number must not exceed 50 characters.'),
            'national_id.required' => __('National ID is required.'),
            'national_id.min'      => __('National ID must be at least 5 characters.'),
            'national_id.max'      => __('National ID must not exceed 30 characters.'),
        ];
    }
}
