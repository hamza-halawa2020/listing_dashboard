<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'listing_id',
        'title',
        'description',
        'price_before_discount',
        'price_after_discount',
        'discount_amount',
        'discount_percentage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_before_discount' => 'decimal:2',
        'price_after_discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Offer $offer): void {
            if (is_numeric($offer->discount_percentage)) {
                $offer->discount_percentage = min(100, max(0, (float) $offer->discount_percentage));
            }

            $pricing = static::calculatePricing(
                $offer->price_before_discount,
                $offer->discount_percentage,
            );

            $offer->discount_amount = $pricing['discount_amount'];
            $offer->price_after_discount = $pricing['price_after_discount'];
        });
    }

    /**
     * @return array{discount_amount: float|null, price_after_discount: float|null}
     */
    public static function calculatePricing(mixed $priceBeforeDiscount, mixed $discountPercentage): array
    {
        if (! is_numeric($priceBeforeDiscount)) {
            return [
                'discount_amount' => null,
                'price_after_discount' => null,
            ];
        }

        $priceBeforeDiscount = round((float) $priceBeforeDiscount, 2);

        if (! is_numeric($discountPercentage)) {
            return [
                'discount_amount' => null,
                'price_after_discount' => $priceBeforeDiscount,
            ];
        }

        $discountPercentage = min(100, max(0, (float) $discountPercentage));
        $discountAmount = round($priceBeforeDiscount * ($discountPercentage / 100), 2);

        return [
            'discount_amount' => $discountAmount,
            'price_after_discount' => round(max($priceBeforeDiscount - $discountAmount, 0), 2),
        ];
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
