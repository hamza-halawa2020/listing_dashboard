<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends ApiController
{
    public function __construct()
    {
        $this->model = Location::class;
        $this->resource = LocationResource::class;
        $this->with = ['children'];

    }

    public function index(Request $request)
    {
        $query = Location::with($this->with)->orderedForDisplay();

        if ($this->paginate) {
            $items = $query->paginate(
                $request->get('limit', $this->perPage)
            );
        } else {
            $items = $query->get();
        }

        return $this->resource::collection($items);
    }
}
