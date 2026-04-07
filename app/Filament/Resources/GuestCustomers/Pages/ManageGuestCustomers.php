<?php

namespace App\Filament\Resources\GuestCustomers\Pages;

use App\Filament\Resources\GuestCustomers\GuestCustomerResource;
use Filament\Resources\Pages\ManageRecords;

class ManageGuestCustomers extends ManageRecords
{
    protected static string $resource = GuestCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
