<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'governorate' => __('Governorate'),
                        'zone' => __('Zone/Area'),
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('Parent Location'))
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->label(__('Shipping Cost'))
                    ->money('egp')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name', fn ($query) => $query->orderedForDisplay())
                    ->label(__('Filter by Parent Location'))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->disabled(fn ($record) => $record->listings()->exists() || $record->children()->exists())
                    ->tooltip(fn ($record) => $record->listings()->exists()
                        ? __('This location has related listings and cannot be deleted')
                        : ($record->children()->exists() ? __('This location has child locations and cannot be deleted') : null)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
