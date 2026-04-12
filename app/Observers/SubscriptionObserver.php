<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Services\SystemNotificationService;

class SubscriptionObserver
{
    public function __construct(private readonly SystemNotificationService $notifications) {}

    public function updated(Subscription $subscription): void
    {
        $subscription->loadMissing('user', 'subscriptionPlan');

        if ($subscription->wasChanged('status')) {
            [$title, $body, $status] = match ($subscription->status) {
                'active' => [
                    __('Subscription Activated'),
                     __('Your subscription is now active and you can enjoy its benefits.'),
                    'success',
                ],
                'expired' => [
                    __('Subscription Expired'),
                    __('Your subscription has expired. You can renew it to continue enjoying the service.'),
                    'warning',
                ],
                'cancelled' => [
                    __('Subscription Cancelled'),
                    __('Your subscription has been cancelled. If this is unexpected, please contact us.'),
                    'danger',
                ],
                default => [
                    __('Subscription Updated'),
                    __('Your subscription has been updated.'),
                    'info',
                ],
            };

            $this->notifications->notifyUser(
                $subscription->user,
                $title,
                $body,
                $status,
                ['source' => 'subscription']
            );
        }

        if ($subscription->wasChanged('is_card_issued') && $subscription->is_card_issued) {
            $planName = $subscription->subscriptionPlan?->name ?: __('a subscription plan');
            $cardNumber = $subscription->membership_card_number ?: __('not available currently');

            $this->notifications->notifyUser(
                $subscription->user,
                __('Membership Card Issued'),
                __('Your membership card for :plan has been issued. Membership number: :number.', [
                    'plan' => $planName,
                    'number' => $cardNumber,
                ]),
                'success',
                ['source' => 'subscription']
            );
        }
    }
}
