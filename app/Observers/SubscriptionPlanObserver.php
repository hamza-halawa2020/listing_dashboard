<?php

namespace App\Observers;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionRewardHistory;

class SubscriptionPlanObserver
{
    public function updating(SubscriptionPlan $plan): void
    {
        if (! $plan->isDirty('subscription_reward_points')) {
            return;
        }

        SubscriptionRewardHistory::create([
            'subscription_plan_id' => $plan->getKey(),
            'old_points'           => (int) $plan->getOriginal('subscription_reward_points', 0),
            'new_points'           => (int) $plan->subscription_reward_points,
            'changed_by_admin_id'  => auth()->id(),
        ]);
    }
}
