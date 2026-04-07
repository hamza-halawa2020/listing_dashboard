<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Http\Resources\Api\CommentResource;

class CommentController extends ApiController
{
    public function __construct()
    {
        $this->model = Comment::class;
        $this->resource = CommentResource::class;
        $this->with = ['createdBy', 'post'];

    }

}
