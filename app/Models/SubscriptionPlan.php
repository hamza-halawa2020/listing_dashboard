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
        'duration_days',
        'max_family_members',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
