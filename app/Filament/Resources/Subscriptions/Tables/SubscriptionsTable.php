<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->sortable(),
                TextColumn::make('subscriptionPlan.name')
                    ->label(__('Plan'))
                    ->sortable(),
                TextColumn::make('membership_card_number')
                    ->label(__('Membership Number'))
                    ->searchable(),
                ToggleColumn::make('is_card_issued')
                    ->label(__('Card Issued'))
                    ->sortable(),
                TextColumn::make('card_issued_at')
                    ->label(__('Card Issued At'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('starts_at')
                    ->label(__('Starts At'))
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('Ends At'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('payment_reference')
                    ->label(__('Payment Reference'))
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('Payment Method'))
                    ->badge(),
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record): bool => SubscriptionResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make()
                    //     ->visible(fn (): bool => SubscriptionResource::canDeleteAny()),
                ]),
            ]);
    }
}
