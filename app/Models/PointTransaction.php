<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'referral_id',
        'created_by_admin_id',
        'type',
        'points',
        'balance_after',
        'note',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer',
    ];

    public static function typeLabels(): array
    {
        return [
            'referral_bonus' => __('Referral bonus'),
            'referee_bonus' => __('Welcome bonus'),
            'signup_bonus' => __('Signup bonus'),
            'subscription_bonus' => __('Subscription bonus'),
            'redeem' => __('Redeem'),
            'admin_add' => __('Admin add'),
            'admin_deduct' => __('Admin deduct'),
            'expire' => __('Expire'),
            'adjustment' => __('Adjustment'),
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}
