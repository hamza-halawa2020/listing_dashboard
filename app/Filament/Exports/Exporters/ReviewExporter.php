<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Review;

class ReviewExporter extends BaseExporter
{
    protected static ?string $model = Review::class;

    protected static array $relationshipColumns = [
        'listing.name',
        'user.name',
    ];

    protected static array $columnLabels = [
        'listing_id'  => 'Listing ID',
        'user_id'     => 'User ID',
        'rating'      => 'Rating',
        'comment'     => 'Comment',
        'status'      => 'Status',
        'listing.name' => 'Business Name',
        'user.name'   => 'User Name',
    ];
}
