<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Http\Resources\Api\ReviewResource;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StoreReviewRequest;
use Illuminate\Support\Facades\Auth;

class ReviewController extends ApiController
{
    public function __construct()
    {
        $this->model = Review::class;
        $this->resource = ReviewResource::class;
        $this->with = ['createdBy'];
    }

    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::guard('sanctum')->user();

        if ($user) {
            $validated['guest_name'] = $user->name;
            $validated['guest_phone'] = $user->phone;
            $validated['guest_email'] = $user->email;
        }

        $review = Review::create([
            ...$validated,
            'created_by' => $user?->id,
        ]);

        return new ReviewResource($review->load($this->with));
    }
}
