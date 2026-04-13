<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['integer', 'exists:users,id', 'distinct'],
            'subject' => ['nullable', 'string', 'max:255'],
        ];
    }
}
