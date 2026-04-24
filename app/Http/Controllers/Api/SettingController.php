<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Http\Resources\Api\SettingResource;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    public function __construct()
    {
        $this->model = Setting::class;
        $this->resource = SettingResource::class;
    }

    public function index(Request $request)
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return response()->json([
            'data' => [
                // Contact
                'phone'     => $settings['phone'] ?? null,
                'whatsapp'  => $settings['whatsapp'] ?? null,
                'instapay'  => $settings['instapay'] ?? null,
                'vodafonecash'  => $settings['vodafonecash'] ?? null,
                // Referral
                'referral_enabled' => filter_var($settings['referral_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ],
        ]);
    }
}
