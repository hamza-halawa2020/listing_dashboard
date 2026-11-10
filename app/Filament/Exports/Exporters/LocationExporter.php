<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Location;

class LocationExporter extends BaseExporter
{
    protected static ?string $model = Location::class;

    protected static array $relationshipColumns = [
        'parent.name',
    ];

    protected static array $columnLabels = [
        'name'           => 'Location Name',
        'type'           => 'Type',
        'parent_id'      => 'Parent Location',
        'shipping_cost'  => 'Shipping Cost',
        'latitude'       => 'Latitude',
        'longitude'      => 'Longitude',
        'parent.name'    => 'Parent Location',
    ];
}
