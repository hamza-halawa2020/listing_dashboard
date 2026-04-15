<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'referral_code' => [
                'nullable',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $exists = User::query()
                        ->whereRaw('UPPER(referral_code) = ?', [Str::upper(trim((string) $value))])
                        ->exists();

                    if (! $exists) {
                        $fail(__('The selected referral code is invalid.'));
                    }
                },
            ],
        ];
    }
}
