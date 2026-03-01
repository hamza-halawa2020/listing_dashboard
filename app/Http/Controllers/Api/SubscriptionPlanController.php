<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Resources\Api\SubscriptionPlanResource;

class SubscriptionPlanController extends ApiController
{
    public function __construct()
    {
        $this->model = SubscriptionPlan::class;
        $this->resource = SubscriptionPlanResource::class;
    }
}
