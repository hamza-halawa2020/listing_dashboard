<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePostCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAuthenticated = Auth::guard('sanctum')->check();

        return [
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
            'guest_name' => $isAuthenticated
                ? ['nullable', 'string', 'max:255']
                : ['required', 'string', 'max:255'],
            'guest_phone' => $isAuthenticated
                ? ['nullable', 'string', 'max:20']
                : ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required' => 'The guest name field is required when you are not logged in.',
            'guest_phone.required' => 'The guest phone field is required when you are not logged in.',
        ];
    }
}
