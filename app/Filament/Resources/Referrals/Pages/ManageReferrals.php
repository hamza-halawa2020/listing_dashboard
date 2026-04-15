<?php

namespace App\Filament\Resources\Referrals\Pages;

use App\Filament\Resources\Referrals\ReferralResource;
use App\Filament\Widgets\ReferralOverviewStats;
use Filament\Resources\Pages\ManageRecords;

class ManageReferrals extends ManageRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReferralOverviewStats::class,
        ];
    }
}
