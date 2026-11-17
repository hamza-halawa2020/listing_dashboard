<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Listing;

class ListingExporter extends BaseExporter
{
    protected static ?string $model = Listing::class;

    protected static array $excludedColumns = [
        'password',
        'remember_token',
    ];

    protected static array $relationshipColumns = [
        'category.name',
        'location.name',
        'offers.description',
    ];

    protected static array $columnLabels = [
        'network_category' => 'Network Category',
        'offers.description' => 'Agreed Prices and Discount Terms',
        'name'        => 'Business Name',
        'category_id' => 'Category',
        'location_id' => 'Location',
        'is_active'   => 'Active',
        'category.name' => 'Category',
        'location.name' => 'Location',
    ];
}
