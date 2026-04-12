<?php

namespace App\Observers;

use App\Models\PriceRequest;
use App\Services\SystemNotificationService;

class PriceRequestObserver
{
    public function __construct(private readonly SystemNotificationService $notifications) {}

    public function created(PriceRequest $priceRequest): void
    {
        $priceRequest->loadMissing('createdBy');

        $companyName = $priceRequest->company_name ?: $priceRequest->contact_person;

        $this->notifications->notifyAdmins(
            __('New Price Request Received'),
             __("A new price request has been received from :company.", ['company' => $companyName]),
            'warning',
            ['source' => 'price_request']
        );

        $this->notifications->notifyUser(
            $priceRequest->createdBy,
            __('New Price Request Received'),
            __("Your price request has been received from :company.", ['company' => $companyName]),
            'info',
            ['source' => 'price_request']
        );
    }

    public function updated(PriceRequest $priceRequest): void
    {
        if (! $priceRequest->wasChanged('status')) {
            return;
        }

        $priceRequest->loadMissing('createdBy');

        if ($priceRequest->status) {
            $body = __('Your price request has been reviewed and you can follow up with our team to complete the details.');

            if (filled($priceRequest->response_notes)) {
                $body .= ' ' . __('Team Notes') . ': ' . $priceRequest->response_notes;
            }

            $this->notifications->notifyUser(
                $priceRequest->createdBy,
                __('Price Request Reviewed'),
                $body,
                'success',
                ['source' => 'price_request']
            );

            return;
        }

        $this->notifications->notifyUser(
            $priceRequest->createdBy,
            __('Price Request Reopened'),
            __('Your price request has been reopened and is now being reviewed again.'),
            'warning',
            ['source' => 'price_request']
        );
    }
}
