<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\PointTransaction;

class PointTransactionExporter extends BaseExporter
{
    protected static ?string $model = PointTransaction::class;

    protected static array $relationshipColumns = [
        'user.name',
        'user.phone',
    ];

    protected static array $columnLabels = [
        'user_id'       => 'User ID',
        'points'        => 'Points',
        'balance_after' => 'Balance After',
        'reason'        => 'Reason',
        'description'   => 'Description',
        'user.name'     => 'User Name',
        'user.phone'    => 'User Phone',
    ];
}
