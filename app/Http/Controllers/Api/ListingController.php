<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Http\Resources\Api\ListingResource;

class ListingController extends ApiController
{
    public function __construct()
    {
        $this->model = Listing::class;
        $this->resource = ListingResource::class;
        $this->with = ['category', 'location'];
    }
}
