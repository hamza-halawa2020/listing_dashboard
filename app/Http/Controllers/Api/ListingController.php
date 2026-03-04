<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Http\Resources\Api\ListingResource;
use App\Models\Category;
use App\Models\Location;
use Carbon\Carbon;

class ListingController extends ApiController
{
    public function __construct()
    {
        $this->model = Listing::class;
        $this->resource = ListingResource::class;
        $this->with = ['category', 'location','workingHours','offers','images','phones', 'links'];
    }

    public function index(Request $request)
    {
        $query = $this->model::with($this->with);

        if (method_exists(new $this->model, 'scopeActive')) {
            $query->active();
        }

        if ($request->has('category_id')) {
            $category = Category::find($request->category_id);
            if ($category) {
                $categoryIds = $category->getBranchIds();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->has('location_id')) {
            $location = Location::find($request->location_id);
            if ($location) {
                $locationIds = $location->getDescendantIds();
                $query->whereIn('location_id', $locationIds);
            }
        }

        if ($request->boolean('open_now')) {
            $now = Carbon::now('Africa/Cairo');
            $dayName = strtolower($now->format('l'));
            $currentTime = $now->format('H:i:s');

            $query->whereHas('workingHours', function ($q) use ($dayName, $currentTime) {
                $q->where('day', $dayName)
                  ->where('is_closed', false)
                  ->where(function ($sub) use ($currentTime) {
                      $sub->whereColumn('open_time', '<=', 'close_time')
                          ->whereTime('open_time', '<=', $currentTime)
                          ->whereTime('close_time', '>=', $currentTime);
                  })
                  ->orWhere(function ($sub) use ($currentTime) {
                      $sub->whereColumn('open_time', '>', 'close_time')
                          ->where(function ($inner) use ($currentTime) {
                              $inner->whereTime('open_time', '<=', $currentTime)
                                    ->orWhereTime('close_time', '>=', $currentTime);
                          });
                  });
            });
        }

        if ($this->paginate) {
            $items = $query->latest()->paginate(
                $request->get('limit', $this->perPage)
            );
        } else {
            $items = $query->latest()->get();
        }

        return $this->resource::collection($items);
    }
}
