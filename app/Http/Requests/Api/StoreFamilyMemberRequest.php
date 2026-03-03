<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamilyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscription_id' => ['nullable', 'exists:subscriptions,id'],
            'name' => ['required', 'string', 'max:255'],
            'national_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('family_members', 'national_id'),
                Rule::unique('users', 'national_id'),
            ],
            'relation' => ['required', Rule::in(['spouse', 'son', 'daughter', 'father', 'mother', 'brother', 'sister'])],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', Rule::in(['male', 'female'])],
        ];
    }
}
