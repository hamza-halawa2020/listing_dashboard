<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
