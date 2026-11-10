<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Filament\Exports\Exporters\SubscriptionExporter;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
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
                    ->searchable(['name', 'national_id', 'phone'])
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
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => __('Active'),
                        'expired' => __('Expired'),
                        'cancelled' => __('Cancelled'),
                        default => $state ?? '-',
                    }),
                TextColumn::make('payment_reference')
                    ->label(__('Payment Reference'))
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('Payment Method'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => __('Cash'),
                        'credit_card' => __('Credit card'),
                        'bank_transfer' => __('Bank transfer'),
                        'free' => __('Free'),
                        'fawry' => __('Fawry'),
                        'instapay' => __('Instapay'),
                        'vodafone_cash' => __('Vodafone cash'),
                        'online' => __('Online'),
                        default => $state ?? '-',
                    }),
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
                ExportAction::make()->exporter(SubscriptionExporter::class),
                BulkActionGroup::make([
                    // DeleteBulkAction::make()
                    //     ->visible(fn (): bool => SubscriptionResource::canDeleteAny()),
                ]),
            ]);
    }
}
