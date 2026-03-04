<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn (): UserResource => new UserResource($this->user)),
            'plan' => new SubscriptionPlanResource($this->whenLoaded('subscriptionPlan')),
            'membership_card_number' => $this->membership_card_number,
            'is_card_issued' => (bool) $this->is_card_issued,
            'card_issued_at' => $this->card_issued_at?->format('Y-m-d H:i:s'),
            'starts_at' => $this->starts_at?->format('Y-m-d'),
            'ends_at' => $this->ends_at?->format('Y-m-d'),
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'notes' => $this->notes,
            'family_members' => $this->whenLoaded(
                'familyMembers',
                fn () => FamilyMemberResource::collection($this->familyMembers),
            ),
            'payments' => CheckSubscriptionPaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
