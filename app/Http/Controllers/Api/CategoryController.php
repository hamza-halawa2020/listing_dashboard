<?php
namespace App\Http\Controllers\Api;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
class CategoryController extends ApiController
{
    public function __construct()
    {
        $this->model = Category::class;
        $this->resource = CategoryResource::class;
        $this->with = ['children' => fn($q) => $q->withCount('listings'), 'parent'];
    }
    public function index(Request $request)
    {
        $query = $this->model::where('slug', '!=', 'other')
            ->with($this->with)
            ->withCount('listings');
        if ($this->paginate) {
            $items = $query->latest()->paginate(
                $request->get('limit', $this->perPage)
            );
        } else {
            $items = $query->latest()->get();
        }
        return $this->resource::collection($items);
    }
    public function withOthers(Request $request)
    {
        $query = $this->model::with($this->with)
            ->withCount('listings');
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
