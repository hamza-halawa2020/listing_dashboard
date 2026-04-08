<?php

namespace App\Http\Controllers\Api;

use App\Models\PriceRequest;
use App\Http\Resources\Api\PriceRequestResource;
use App\Http\Requests\Api\StorePriceRequest;
use Illuminate\Http\Request;

class PriceRequestController extends ApiController
{
    public function __construct()
    {
        $this->model = PriceRequest::class;
        $this->resource = PriceRequestResource::class;
        $this->with = ['createdBy', 'respondedBy'];
    }

    public function store(StorePriceRequest $request)
    {
        $validated = $request->validated();

        $priceRequest = PriceRequest::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return new PriceRequestResource($priceRequest->load($this->with));
    }
}
