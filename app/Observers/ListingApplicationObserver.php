<?php

namespace App\Observers;

use App\Models\ListingApplication;
use App\Services\SystemNotificationService;

class ListingApplicationObserver
{
    public function __construct(private readonly SystemNotificationService $notifications) {}

    public function created(ListingApplication $application): void
    {
        $application->loadMissing('listing', 'user');

        $listingName = $application->listing?->name ?: __('a listing');
        $contactName = $application->contact_name ?: __('A contact');
        $body = __(':contact has submitted a new listing application for :listing.', [
            'contact' => $contactName,
            'listing' => $listingName,
        ]);

        $this->notifications->notifyAdmins(
            __('New Listing Application Received'),
            $body,
            'warning',
            ['source' => 'listing_application']
        );

        $this->notifications->notifyUser(
            $application->user,
            __('Listing Application Received'),
            __('Your listing application has been received successfully and will be reviewed by our team soon.'),
            'info',
            ['source' => 'listing_application']
        );
    }

    public function updated(ListingApplication $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        $application->loadMissing('user', 'listing');

        if ($application->status === 'approved') {
            $this->notifications->notifyUser(
                $application->user,
                __('Listing Application Approved'),
                __('Your listing application has been approved and your listing is now active.'),
                'success',
                ['source' => 'listing_application']
            );

            return;
        }

        if ($application->status === 'rejected') {
            $body = __('Your listing application has been rejected.');

            if (filled($application->rejection_reason)) {
                $body .= ' ' . __('Rejection Reason') . ': ' . $application->rejection_reason;
            }

            $this->notifications->notifyUser(
                $application->user,
                __('Listing Application Rejected'),
                $body,
                'danger',
                ['source' => 'listing_application']
            );
        }
    }
}
