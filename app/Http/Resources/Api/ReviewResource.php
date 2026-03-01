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
            'createdBy' => $this->createdBy->name,
            'review' => $this->review,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
