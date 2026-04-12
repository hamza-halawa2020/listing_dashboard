<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePostCommentRequest;
use App\Http\Resources\Api\CommentResource;
use App\Models\Contact;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostCommentController extends ApiController
{
    public function store(StorePostCommentRequest $request, Post $post): JsonResponse
    {
        abort_unless($post->status, 404);

        $user = Auth::guard('sanctum')->user();
        $validated = $request->validated();

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'comment' => $validated['comment'],
            'created_by' => $user?->id,
            'guest_name' => $user ? null : $validated['guest_name'],
            'guest_phone' => $user ? null : $validated['guest_phone'],
            'status' => false,
        ]);

        if (! $user) {
            $guestCustomer = Contact::query()->firstOrNew([
                'phone' => $validated['guest_phone'],
                'source' => 'guest_comment',
            ]);

            $guestCustomer->name = $validated['guest_name'];
            $guestCustomer->message = $validated['comment'];
            $guestCustomer->comment_count = $guestCustomer->exists
                ? $guestCustomer->comment_count + 1
                : 1;
            $guestCustomer->last_commented_at = now();
            $guestCustomer->save();
        }

        return (new CommentResource($comment->load(['createdBy', 'post'])))
            ->additional([
                'message' => __('Your comment has been submitted successfully and is awaiting approval.'),
            ])
            ->response()
            ->setStatusCode(201);
    }
}
