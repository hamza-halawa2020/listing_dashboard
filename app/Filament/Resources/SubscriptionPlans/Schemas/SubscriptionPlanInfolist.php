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
                    ->label(__('Name'))
                    ->formatStateUsing(fn (?string $state): ?string => filled($state) ? __($state) : $state),
                TextEntry::make('code')
                    ->label(__('Code')),
                TextEntry::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (?string $state): ?string => match ($state) {
                        'individual' => __('Individual'),
                        'family' => __('Family'),
                        default => $state,
                    })
                    ->badge(),
                TextEntry::make('coverage_type')
                    ->label(__('Coverage Type'))
                    ->formatStateUsing(fn (?string $state): ?string => match ($state) {
                        'zone' => __('Zone'),
                        'governorate' => __('Governorate'),
                        'national' => __('National'),
                        default => $state,
                    })
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
