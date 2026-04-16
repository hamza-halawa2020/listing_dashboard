<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointSetting extends Model
{
    protected $fillable = [
        'points_to_egp_rate',
        'notes',
    ];

    protected $casts = [
        'points_to_egp_rate' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::updated(function (PointSetting $pointSetting): void {
            if (! $pointSetting->wasChanged('points_to_egp_rate')) {
                return;
            }

            PointRateHistory::create([
                'old_rate' => $pointSetting->getOriginal('points_to_egp_rate'),
                'new_rate' => $pointSetting->points_to_egp_rate,
                'reason' => $pointSetting->reason ?? __('point-settings.defaults.reason'),
                'changed_by_admin_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Get the current point rate
     */
    public static function getCurrentRate(): float
    {
        return static::first()?->points_to_egp_rate ?? 0.1000;
    }

    /**
     * Calculate points needed for a given amount
     */
    public static function calculatePointsNeeded(float $amount): int
    {
        $rate = static::getCurrentRate();
        return (int) ceil($amount / $rate);
    }

    /**
     * Calculate EGP value for given points
     */
    public static function calculateEgpValue(int $points): float
    {
        $rate = static::getCurrentRate();
        return $points * $rate;
    }
}
