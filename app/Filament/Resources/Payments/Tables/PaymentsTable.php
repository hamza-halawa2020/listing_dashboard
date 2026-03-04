<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('attachment')
                    ->label(__('Image'))
                    ->square(),
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->sortable(),
                TextColumn::make('subscription.id')
                    ->label(__('Subscription #'))
                    ->sortable(),
                TextColumn::make('subscription.membership_card_number')
                    ->label(__('Membership Number'))
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('egp')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label(__('Payment Method'))
                    ->badge(),
                TextColumn::make('transaction_reference')
                    ->label(__('Transaction Reference'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('paid_at')
                    ->label(__('Paid At'))
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
                ViewAction::make()
                    ->visible(fn ($record): bool => PaymentResource::canView($record)),
                EditAction::make()
                    ->visible(fn ($record): bool => PaymentResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
