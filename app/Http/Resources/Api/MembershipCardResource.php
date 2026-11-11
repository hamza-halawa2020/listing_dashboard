<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'card_number'              => $this->card_number,
            'status'                   => $this->status,
            'subscription_plan_id'      => $this->subscription_plan_id,
            'plan'                     => $this->whenLoaded('subscriptionPlan', fn () => new SubscriptionPlanResource($this->subscriptionPlan)),
            'starts_at'                => $this->starts_at?->format('Y-m-d'),
            'ends_at'                  => $this->ends_at?->format('Y-m-d'),
            'national_id'              => $this->national_id,
            'activation_requested_at'  => $this->activation_requested_at?->format('Y-m-d H:i:s'),
            'activation_approved_at'   => $this->activation_approved_at?->format('Y-m-d H:i:s'),
            'notes'                    => $this->notes,
            'created_at'               => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
