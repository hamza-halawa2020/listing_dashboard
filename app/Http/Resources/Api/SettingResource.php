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
            'logo_url' => $this->logo ? asset('storage/' . $this->logo) : null,
        ];
    }
}
