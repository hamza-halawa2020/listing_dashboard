<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'email' => $this->email,
            'address' => $this->address,
            'about_us' => $this->about_us,
            'about_us_footer' => $this->about_us_footer,
            'privacy_policy' => $this->privacy_policy,
            'terms_conditions' => $this->terms_conditions,
            'referral_enabled' => $this->booleanValue($this->referral_enabled ?? false),
            'referral_trigger' => $this->referral_trigger ?? null,
            'referrer_reward_points' => $this->integerValue($this->referrer_reward_points ?? 0),
            'referee_reward_type' => $this->referee_reward_type ?? null,
            'referee_reward_value' => $this->floatValue($this->referee_reward_value ?? 0),
            'referral_min_payment_amount' => $this->floatValue($this->referral_min_payment_amount ?? 0),
            'logo_url' => $this->logo ? asset('storage/' . $this->logo) : null,
        ];
    }

    private function booleanValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function integerValue(mixed $value): int
    {
        return max((int) $value, 0);
    }

    private function floatValue(mixed $value): float
    {
        return round(max((float) $value, 0), 2);
    }
}
