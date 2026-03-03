<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email address'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('Role'))
                    ->badge(),
                TextColumn::make('national_id')
                    ->label(__('National ID'))
                    ->searchable(),
                TextColumn::make('location.name')
                    ->label(__('Location'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label(__('Birth Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->badge(),
                TextColumn::make('email_verified_at')
                    ->label(__('Email Verified At'))
                    ->dateTime()
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
