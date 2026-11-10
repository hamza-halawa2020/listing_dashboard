<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\User;

class UserExporter extends BaseExporter
{
    protected static ?string $model = User::class;

    protected static array $excludedColumns = [
        'password',
        'remember_token',
        'email_verified_at',
        'referred_by_user_id',
    ];

    protected static array $columnLabels = [
        'name'           => 'Name',
        'phone'          => 'Phone',
        'national_id'    => 'National ID',
        'referral_code'  => 'Referral Code',
        'points_balance' => 'Points Balance',
        'role'           => 'Role',
        'location_id'    => 'Location ID',
        'birth_date'     => 'Birth Date',
        'gender'         => 'Gender',
        'address'        => 'Address',
    ];
}
