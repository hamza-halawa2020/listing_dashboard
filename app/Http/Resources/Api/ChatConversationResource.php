<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        $lastReadAt = $this->participants
            ->firstWhere('id', $authUser?->id)
            ?->pivot?->last_read_at;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'subject' => $this->subject,
            'last_message_at' => $this->last_message_at?->toDateTimeString(),
            'participants' => $this->participants->map(fn ($participant) => [
                'id' => $participant->id,
                'name' => $participant->name,
                'role' => $participant->role,
                'last_read_at' => $participant->pivot?->last_read_at,
            ])->values(),
            'latest_message' => $this->whenLoaded('messages', fn () => $this->messages->last() ? new ChatMessageResource($this->messages->last()) : null),
            'unread_count' => $authUser
                ? $this->messages
                    ->where('sender_id', '!=', $authUser->id)
                    ->filter(fn ($message) => blank($lastReadAt) || $message->created_at?->gt($lastReadAt))
                    ->count()
                : 0,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
