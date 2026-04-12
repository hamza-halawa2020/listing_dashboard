<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatabaseNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'status' => $data['status'] ?? 'info',
            'icon' => $data['icon'] ?? null,
            'source' => $data['source'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'is_read' => filled($this->read_at),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'data' => $data,
        ];
    }
}
