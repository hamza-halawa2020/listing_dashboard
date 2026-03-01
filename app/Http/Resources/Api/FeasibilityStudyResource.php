<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeasibilityStudyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'money_capital' => $this->money_capital,
            'rate_of_return' => $this->rate_of_return,
            'services' => $this->services,
            'study_content' => $this->study_content,
            'financial_metrics' => $this->financial_metrics,
            'status' => $this->status,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
