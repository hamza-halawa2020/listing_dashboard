<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_REWARDED = 'rewarded';
    public const STATUS_REJECTED = 'rejected';

    public const TRIGGER_REGISTER = 'register';
    public const TRIGGER_FIRST_PAYMENT = 'first_payment';
    public const TRIGGER_FIRST_SUBSCRIPTION = 'first_subscription';

    public const REWARD_NONE = 'none';
    public const REWARD_POINTS = 'points';
    public const REWARD_FIXED_DISCOUNT = 'fixed_discount';
    public const REWARD_PERCENT_DISCOUNT = 'percent_discount';

    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'referral_code_used',
        'status',
        'trigger_type',
        'qualified_payment_id',
        'qualified_subscription_id',
        'referee_reward_payment_id',
        'referrer_points_awarded',
        'referee_reward_type',
        'referee_reward_value',
        'referee_points_awarded',
        'referee_discount_amount_applied',
        'qualified_at',
        'rewarded_at',
        'referee_reward_applied_at',
        'notes',
    ];

    protected $casts = [
        'qualified_at' => 'datetime',
        'rewarded_at' => 'datetime',
        'referee_reward_applied_at' => 'datetime',
        'referee_reward_value' => 'decimal:2',
        'referee_discount_amount_applied' => 'decimal:2',
        'referrer_points_awarded' => 'integer',
        'referee_points_awarded' => 'integer',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_QUALIFIED => __('Qualified'),
            self::STATUS_REWARDED => __('Rewarded'),
            self::STATUS_REJECTED => __('Rejected'),
        ];
    }

    public static function triggerLabels(): array
    {
        return [
            self::TRIGGER_REGISTER => __('On registration'),
            self::TRIGGER_FIRST_PAYMENT => __('On first completed payment'),
            self::TRIGGER_FIRST_SUBSCRIPTION => __('On first completed subscription payment'),
        ];
    }

    public static function rewardTypeLabels(): array
    {
        return [
            self::REWARD_NONE => __('None'),
            self::REWARD_POINTS => __('Points'),
            self::REWARD_FIXED_DISCOUNT => __('Fixed discount'),
            self::REWARD_PERCENT_DISCOUNT => __('Percent discount'),
        ];
    }

    public static function discountRewardTypes(): array
    {
        return [
            self::REWARD_FIXED_DISCOUNT,
            self::REWARD_PERCENT_DISCOUNT,
        ];
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function qualifiedPayment()
    {
        return $this->belongsTo(Payment::class, 'qualified_payment_id');
    }

    public function qualifiedSubscription()
    {
        return $this->belongsTo(Subscription::class, 'qualified_subscription_id');
    }

    public function refereeRewardPayment()
    {
        return $this->belongsTo(Payment::class, 'referee_reward_payment_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }
}
