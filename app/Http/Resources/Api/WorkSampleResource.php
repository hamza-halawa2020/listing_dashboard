<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkSampleResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'money_capital' => $this->money_capital,
            'rate_of_return' => $this->rate_of_return,
            'services' => $this->services,
            'study_content' => $this->study_content,
            'financial_metrics' => $this->financial_metrics,
            'status' => $this->status,
        ];
    }
}
