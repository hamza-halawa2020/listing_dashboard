<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'location_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'status',
        'attachment',
        'notes',
        'delivery_required',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'shipping_cost',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'delivery_required' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (Payment $payment) {
            if ($payment->status !== 'completed' || ! $payment->subscription_id) {
                return;
            }

            if (blank($payment->paid_at)) {
                $payment->forceFill([
                    'paid_at' => now(),
                ])->saveQuietly();
            }

            $payment->loadMissing('subscription.subscriptionPlan', 'subscription.user');

            $subscription = $payment->subscription;

            if (! $subscription || filled($subscription->membership_card_number)) {
                return;
            }

            $subscription->forceFill([
                'membership_card_number' => $subscription->generateMembershipCardNumber(),
            ])->saveQuietly();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
