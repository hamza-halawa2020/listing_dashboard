<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\Category;
use App\Http\Resources\Api\CategoryResource;

class CategoryController extends ApiController
{
    public function __construct()
    {
        $this->model = Category::class;
        $this->resource = CategoryResource::class;
        $this->with = ['children', 'listings','parent'];
    }

    public function index(Request $request)
    {
        // Filter categories to only those that have at least one listing
        $query = $this->model::with($this->with)->has('listings');

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
