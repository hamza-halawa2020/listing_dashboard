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
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('code')
                    ->label(__('Code')),
                TextEntry::make('type')
                    ->label(__('Type'))
                    ->badge(),
                TextEntry::make('coverage_type')
                    ->label(__('Coverage Type'))
                    ->badge(),
                TextEntry::make('price')
                    ->label(__('Price'))
                    ->money('egp'),
                TextEntry::make('duration_days')
                    ->label(__('Duration Days'))
                    ->numeric(),
                TextEntry::make('max_family_members')
                    ->label(__('Max Family Members'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
