<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'shipping_cost' => $this->shipping_cost,
            'children' => LocationResource::collection($this->whenLoaded('children')),
        ];
    }
}
