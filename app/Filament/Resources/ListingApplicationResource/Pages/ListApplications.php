<?php

namespace App\Filament\Resources\ListingApplicationResource\Pages;

use App\Filament\Resources\ListingApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ListingApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
