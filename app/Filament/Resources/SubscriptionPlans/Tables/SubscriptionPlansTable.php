<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(fn (?string $state): ?string => filled($state) ? __($state) : $state)
                    ->searchable(),
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (?string $state): ?string => match ($state) {
                        'individual' => __('Individual'),
                        'family' => __('Family'),
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('coverage_type')
                    ->label(__('Coverage Type'))
                    ->formatStateUsing(fn (?string $state): ?string => match ($state) {
                        'zone' => __('Zone'),
                        'governorate' => __('Governorate'),
                        'national' => __('National'),
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('egp')
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label(__('Duration Days'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_family_members')
                    ->label(__('Max Family Members'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record): bool => SubscriptionPlanResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => SubscriptionPlanResource::canDeleteAny()),
                ]),
            ]);
    }
}
