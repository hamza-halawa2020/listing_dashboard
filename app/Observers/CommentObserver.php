<?php

namespace App\Observers;

use App\Models\Comment;
use App\Services\SystemNotificationService;

class CommentObserver
{
    public function __construct(private readonly SystemNotificationService $notifications) {}

    public function created(Comment $comment): void
    {
        $comment->loadMissing('createdBy', 'post');

        $author = $comment->author_name ?: __('Guest');
        $body = filled($comment->post?->title)
            ? __(':author has submitted a new comment on post :postTitle.', [
                'author' => $author,
                'postTitle' => $comment->post->title,
            ])
            : __(':author has submitted a new comment.', ['author' => $author]);

        $this->notifications->notifyAdmins(
            __('New Comment Awaiting Review'),
            $body,
            'warning',
            ['source' => 'comment']
        );

        $this->notifications->notifyUser(
            $comment->createdBy,
            __('Comment Received'),
            __('Your comment has been received successfully and will be reviewed before publication.'),
            'info',
            ['source' => 'comment']
        );
    }

    public function updated(Comment $comment): void
    {
        if (! $comment->wasChanged('status')) {
            return;
        }

        $comment->loadMissing('createdBy');

        if ($comment->status) {
            $this->notifications->notifyUser(
                $comment->createdBy,
                __('Comment Accepted'),
                __('Your comment has been accepted and is now visible on the site.'),
                'success',
                ['source' => 'comment']
            );

            return;
        }

        $this->notifications->notifyUser(
            $comment->createdBy,
            __('Comment Rejected'),
            __('Your comment has been rejected and will not be published.'),
            'warning',
            ['source' => 'comment']
        );
    }
}
