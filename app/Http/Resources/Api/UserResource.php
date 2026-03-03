<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'national_id' => $this->national_id,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'address' => $this->address,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'location' => new LocationResource($this->whenLoaded('location')),
            'family_members' => FamilyMemberResource::collection($this->whenLoaded('familyMembers')),
            'subscriptions' => SubscriptionResource::collection($this->whenLoaded('subscriptions')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
