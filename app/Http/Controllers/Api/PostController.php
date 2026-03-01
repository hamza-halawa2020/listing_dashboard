<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Resources\Api\PostResource;

class PostController extends ApiController
{
    public function __construct()
    {
        $this->model = Post::class;
        $this->resource = PostResource::class;
    }
}
