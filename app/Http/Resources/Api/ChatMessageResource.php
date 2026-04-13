<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->chat_conversation_id,
            'body' => $this->body,
            'meta' => $this->meta,
            'sender' => [
                'id' => $this->sender?->id,
                'name' => $this->sender?->name,
                'role' => $this->sender?->role,
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
