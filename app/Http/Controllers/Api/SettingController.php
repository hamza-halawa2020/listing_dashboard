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
        
        if (empty($settings)) {
            return response()->json(['data' => (object)[]]);
        }

        return new SettingResource((object)$settings);
    }
}
