<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'post_title' => $this->post?->title,
            'author_name' => $this->author_name,
            'author_phone' => $this->author_phone,
            'is_guest' => $this->is_guest,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
