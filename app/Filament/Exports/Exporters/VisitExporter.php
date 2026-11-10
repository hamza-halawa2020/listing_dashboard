<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Visit;

class VisitExporter extends BaseExporter
{
    protected static ?string $model = Visit::class;

    protected static array $excludedColumns = [
        'attachments',
    ];

    protected static array $relationshipColumns = [
        'user.name',
        'user.phone',
        'listing.name',
    ];

    protected static array $columnLabels = [
        'user_id'     => 'User ID',
        'listing_id'  => 'Listing ID',
        'visited_at'  => 'Visited At',
        'status'      => 'Status',
        'notes'       => 'Notes',
        'user.name'   => 'User Name',
        'user.phone'  => 'User Phone',
        'listing.name' => 'Business Name',
    ];
}
