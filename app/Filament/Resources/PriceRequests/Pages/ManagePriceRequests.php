<?php

namespace App\Filament\Resources\PriceRequests\Pages;

use App\Filament\Resources\PriceRequests\PriceRequestResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePriceRequests extends ManageRecords
{
    protected static string $resource = PriceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
