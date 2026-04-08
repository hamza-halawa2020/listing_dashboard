<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_name' => $this->author_name,
            'author_phone' => $this->author_phone,
            'author_email' => $this->author_email,
            'is_guest' => $this->is_guest,
            'review' => $this->review,
            'rating' => $this->rating,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
