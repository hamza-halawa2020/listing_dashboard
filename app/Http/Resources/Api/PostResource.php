<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'excerpt' => Str::limit(strip_tags((string) $this->description), 180),
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'comments_count' => $this->comments_count ?? $this->approvedComments()->count(),
            'created_at' => $this->created_at,
            'author_name' => $this->user?->name,
            'comments' => CommentResource::collection($this->whenLoaded('approvedComments')),
        ];
    }
}
