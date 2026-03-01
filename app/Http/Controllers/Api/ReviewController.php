<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Http\Resources\Api\ReviewResource;

class ReviewController extends ApiController
{

    
    public function __construct()
    {
        $this->model = Review::class;
        $this->resource = ReviewResource::class;
    }

}
