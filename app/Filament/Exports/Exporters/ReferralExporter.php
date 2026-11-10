<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Referral;

class ReferralExporter extends BaseExporter
{
    protected static ?string $model = Referral::class;

    protected static array $relationshipColumns = [
        'referrer.name',
        'referredUser.name',
        'referredUser.phone',
    ];

    protected static array $columnLabels = [
        'referrer_id'           => 'Referrer ID',
        'referred_user_id'      => 'Referred User ID',
        'referral_code_used'    => 'Referral Code',
        'reward_status'         => 'Reward Status',
        'applied_discount'      => 'Applied Discount',
        'qualified_payment_id'  => 'Qualified Payment ID',
        'rewarded_at'           => 'Rewarded At',
        'referrer.name'         => 'Referrer Name',
        'referredUser.name'     => 'Referred User Name',
        'referredUser.phone'    => 'Referred User Phone',
    ];
}
