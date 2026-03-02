<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state) => match($state) {
                        'governorate' => 'Governorate',
                        'zone' => 'zone/area',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Parent Location')
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->label('Shipping Cost')
                    ->money('egp')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->label('Filter by Parent Location')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->disabled(fn ($record) => $record->listings()->exists() || $record->children()->exists())
                    ->tooltip(fn ($record) => $record->listings()->exists()
                            ? 'This location has related listings and cannot be deleted'
                            : ($record->children()->exists() ? 'This location has child locations and cannot be deleted' : null)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
