<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Subscription;

class SubscriptionExporter extends BaseExporter
{
    protected static ?string $model = Subscription::class;

    protected static array $relationshipColumns = [
        'user.name',
        'user.phone',
        'user.national_id',
        'subscriptionPlan.name',
    ];

    protected static array $columnLabels = [
        'user_id'               => 'User ID',
        'subscription_plan_id'  => 'Plan ID',
        'membership_card_number' => 'Membership Number',
        'is_card_issued'        => 'Card Issued',
        'card_issued_at'        => 'Card Issued At',
        'starts_at'             => 'Starts At',
        'ends_at'               => 'Ends At',
        'status'                => 'Status',
        'payment_method'        => 'Payment Method',
        'payment_reference'     => 'Payment Reference',
        'user.name'             => 'User Name',
        'user.phone'            => 'User Phone',
        'user.national_id'      => 'National ID',
        'subscriptionPlan.name' => 'Plan',
    ];
}
