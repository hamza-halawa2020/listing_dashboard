<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Http\Resources\Api\LocationResource;

class LocationController extends ApiController
{
    public function __construct()
    {
        $this->model = Location::class;
        $this->resource = LocationResource::class;
    }
}
