<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends ApiController
{
    public function __construct()
    {
        $this->model = Post::class;
        $this->resource = PostResource::class;
    }

    public function index(Request $request)
    {
        $posts = Post::query()
            ->active()
            ->withCount(['approvedComments as comments_count'])
            ->latest()
            ->paginate($request->integer('limit', $this->perPage));

        return PostResource::collection($posts);
    }

    public function show($id)
    {
        $post = Post::query()
            ->active()
            ->findOrFail($id);

        $post->increment('views_count');

        $post = Post::query()
            ->active()
            ->with([
                'approvedComments' => fn ($query) => $query
                    ->with(['createdBy', 'post'])
                    ->latest(),
            ])
            ->withCount(['approvedComments as comments_count'])
            ->findOrFail($id);

        return new PostResource($post);
    }
}
