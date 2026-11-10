<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\FamilyMember;

class FamilyMemberExporter extends BaseExporter
{
    protected static ?string $model = FamilyMember::class;

    protected static array $relationshipColumns = [
        'user.name',
        'user.phone',
        'subscription.membership_card_number',
    ];

    protected static array $columnLabels = [
        'user_id'         => 'User ID',
        'subscription_id' => 'Subscription ID',
        'name'            => 'Member Name',
        'national_id'     => 'National ID',
        'phone'           => 'Phone',
        'relation'        => 'Relation',
        'birth_date'      => 'Birth Date',
        'user.name'       => 'User Name',
        'user.phone'      => 'User Phone',
        'subscription.membership_card_number' => 'Membership Number',
    ];
}
