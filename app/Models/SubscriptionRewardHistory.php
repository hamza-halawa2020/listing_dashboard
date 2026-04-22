<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionRewardHistory extends Model
{
    protected $table = 'subscription_reward_history';

    protected $fillable = [
        'subscription_plan_id',
        'old_points',
        'new_points',
        'reason',
        'changed_by_admin_id',
    ];

    protected $casts = [
        'old_points' => 'integer',
        'new_points' => 'integer',
    ];

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function changedByAdmin()
    {
        return $this->belongsTo(User::class, 'changed_by_admin_id');
    }
}
