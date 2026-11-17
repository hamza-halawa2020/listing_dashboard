<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
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
            'address' => $this->address,
            'description' => $this->description,
            'network_category' => $this->network_category,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_search_url' => $this->resource->mapSearchUrl(),
            'is_active' => $this->is_active,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'working_hours' => $this->whenLoaded('workingHours'),
            'offers' => $this->whenLoaded('offers'),
            'images' => $this->whenLoaded('images'),
            'phones' => $this->whenLoaded('phones'),
            'links' => $this->whenLoaded('links'),
            'distance' => $this->when($this->distance !== null, function() {
                return round($this->distance, 2);
            }),
        ];
    }
}
