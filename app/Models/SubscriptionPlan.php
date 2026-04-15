<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'coverage_type',
        'price',
        'points_price',
        'duration_days',
        'max_family_members',
        'referrer_reward_points',
        'referee_reward_type',
        'referee_reward_value',
    ];

    protected $casts = [
        'referrer_reward_points' => 'integer',
        'referee_reward_value' => 'decimal:2',
        'points_price' => 'integer',
    ];

    protected $appends = [
        'points_price_calculated',
        'points_price_formatted',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Calculate points needed based on current rate
     */
    public function getPointsPriceCalculatedAttribute(): int
    {
        // Always calculate based on price and current rate
        return PointSetting::calculatePointsNeeded((float) $this->price);
    }

    /**
     * Get formatted points price with currency
     */
    public function getPointsPriceFormattedAttribute(): string
    {
        return number_format($this->points_price_calculated) . ' points';
    }

    /**
     * Check if plan is available for points redemption
     */
    public function isAvailableForPoints(): bool
    {
        return $this->price > 0; // Any plan with price can be redeemed with points
    }

    /**
     * Get EGP value of points price
     */
    public function getPointsEgpValue(): float
    {
        return PointSetting::calculateEgpValue($this->points_price_calculated);
    }
}
