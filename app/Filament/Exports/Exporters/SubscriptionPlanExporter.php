<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\SubscriptionPlan;

class SubscriptionPlanExporter extends BaseExporter
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static array $columnLabels = [
        'name'                        => 'Plan Name',
        'code'                        => 'Code',
        'is_public'                   => 'Visible to Users',
        'type'                        => 'Type',
        'coverage_type'               => 'Coverage Type',
        'price'                       => 'Price',
        'points_price'                => 'Points Price',
        'duration_days'               => 'Duration (Days)',
        'max_family_members'          => 'Max Family Members',
        'subscription_reward_points'  => 'Subscription Reward Points',
        'referrer_reward_points'      => 'Referrer Reward Points',
        'referee_reward_type'         => 'Referee Reward Type',
        'referee_reward_value'        => 'Referee Reward Value',
    ];
}
