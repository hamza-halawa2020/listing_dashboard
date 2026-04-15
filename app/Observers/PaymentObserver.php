<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\ReferralService;
use App\Services\SystemNotificationService;

class PaymentObserver
{
    public function __construct(
        private readonly SystemNotificationService $notifications,
        private readonly ReferralService $referrals,
    ) {}

    public function created(Payment $payment): void
    {
        $payment->loadMissing('user');

        $userName = $payment->user?->name ?: __('Guest');
        $body = __('A new payment of :amount has been received from :user.', [
            'amount' => $payment->amount,
            'user' => $userName,
        ]);

        $this->notifications->notifyAdmins(
            __('New Payment Received'),
            $body,
            'warning',
            ['source' => 'payment']
        );

        $this->notifications->notifyUser(
            $payment->user,
            __('New Payment Received'),
            $body,
            'info',
            ['source' => 'payment']
        );
    }

    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        if ($payment->status === 'completed') {
            $this->referrals->processCompletedPayment($payment);
        }

        if (in_array($payment->status, ['failed', 'refunded'], true)) {
            $this->referrals->releaseDiscountReservation($payment);
        }

        $payment->loadMissing('user');

        [$title, $body, $status] = match ($payment->status) {
            'completed' => [
                __('Payment Confirmed'),
                __('Your payment has been confirmed successfully.'),
                'success',
            ],
            'failed' => [
                __('Payment Failed'),
                 __('Unfortunately, your payment could not be processed. Please check with support or try again.'),
                'danger',
            ],
            'canceled' => [
                __('Payment Canceled'),
                 __('Your payment has been canceled. If this was a mistake, please try again or contact support.'),
                'warning',
            ],
            'rejected' => [
                __('Payment Rejected'),
                __('Unfortunately, your payment was not approved. Please check with support or try again.'),
                'danger',
            ],
            'refunded' => [
                __('Payment Refunded'),
                __('Your payment has been refunded. If you need further assistance, please contact support.'),
                'warning',
            ],
            default => [
                __('Payment Status Updated'),
                __('Your payment status has been updated and is currently being reviewed.'),
                'info',
            ],
        };

        $this->notifications->notifyUser(
            $payment->user,
            $title,
            $body,
            $status,
            ['source' => 'payment']
        );
    }
}
