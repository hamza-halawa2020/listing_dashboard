<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_id' => $this->subscription_id,
            'name' => $this->name,
            'national_id' => $this->national_id,
            'relation' => $this->relation,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'subscription' => $this->whenLoaded('subscription', fn (): array => [
                'id' => $this->subscription->id,
                'membership_card_number' => $this->subscription->membership_card_number,
                'plan' => $this->subscription->subscriptionPlan?->name
                    ? __($this->subscription->subscriptionPlan->name)
                    : null,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
