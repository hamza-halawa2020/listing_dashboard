<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\SystemNotificationService;

class ReviewObserver
{
    public function __construct(private readonly SystemNotificationService $notifications) {}

    public function created(Review $review): void
    {
        $review->loadMissing('createdBy');

        $author = $review->author_name ?: __('Guest');
        $body = __(':author has submitted a new review.', ['author' => $author]);

        $this->notifications->notifyAdmins(
            __('New Review Awaiting Approval'),
            $body,
            'warning',
            ['source' => 'review']
        );

        $this->notifications->notifyUser(
            $review->createdBy,
            __('Review Submitted'),
            __('Thank you, your review has been submitted and will be displayed after approval.'),
            'info',
            ['source' => 'review']
        );
    }

    public function updated(Review $review): void
    {
        if (! $review->wasChanged('status')) {
            return;
        }

        $review->loadMissing('createdBy');

        if ($review->status) {
            $this->notifications->notifyUser(
                $review->createdBy,
                __('Review Published'),
                __('Your review has been published and is now visible on the site.'),
                'success',
                ['source' => 'review']
            );

            return;
        }

        $this->notifications->notifyUser(
            $review->createdBy,
            __('Review Awaiting Approval'),
            __('Your review is still awaiting approval or has not been approved yet.'),
            'warning',
            ['source' => 'review']
        );
    }
}
