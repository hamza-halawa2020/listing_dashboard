<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('code'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('coverage_type')
                    ->badge(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('duration_days')
                    ->numeric(),
                TextEntry::make('max_family_members')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
