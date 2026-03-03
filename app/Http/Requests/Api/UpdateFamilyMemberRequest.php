<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFamilyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $familyMemberId = $this->route('id');

        return [
            'subscription_id' => ['sometimes', 'nullable', 'exists:subscriptions,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'national_id' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('family_members', 'national_id')->ignore($familyMemberId),
                Rule::unique('users', 'national_id'),
            ],
            'relation' => ['sometimes', Rule::in(['spouse', 'son', 'daughter', 'father', 'mother', 'brother', 'sister'])],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female'])],
        ];
    }
}
