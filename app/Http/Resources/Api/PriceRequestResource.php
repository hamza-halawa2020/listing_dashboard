<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_type' => $this->company_type,
            'company_type_label' => $this->company_type_label,
            'employee_count' => $this->employee_count,
            'services_needed' => $this->services_needed,
            'additional_requirements' => $this->additional_requirements,
            'budget_range' => $this->budget_range,
            'budget_range_label' => $this->budget_range_label,
            'timeline' => $this->timeline,
            'timeline_label' => $this->timeline_label,
            'status' => $this->status,
            'responded_at' => $this->responded_at,
            'response_notes' => $this->response_notes,
            'created_at' => $this->created_at,
        ];
    }
}
