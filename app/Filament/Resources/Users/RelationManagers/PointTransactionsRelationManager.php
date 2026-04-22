<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\PointTransaction;
use App\Models\PointSetting;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PointTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointTransactions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        $balance = (int) $ownerRecord->points_balance;
        return __('Points') . ' — ' . number_format($balance) . ' ' . __('pts');
    }

    public function table(Table $table): Table
    {
        $rate        = PointSetting::getCurrentRate();
        $typeLabels  = PointTransaction::typeLabels();

        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->columns([

                // Type badge
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $typeLabels[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'signup_bonus', 'subscription_bonus',
                        'referral_bonus', 'referee_bonus',
                        'admin_add'      => 'success',
                        'redeem',
                        'admin_deduct',
                        'expire'         => 'danger',
                        default          => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'signup_bonus'       => 'heroicon-m-user-plus',
                        'subscription_bonus' => 'heroicon-m-identification',
                        'referral_bonus'     => 'heroicon-m-share',
                        'referee_bonus'      => 'heroicon-m-gift',
                        'redeem'             => 'heroicon-m-shopping-cart',
                        'admin_add'          => 'heroicon-m-plus-circle',
                        'admin_deduct'       => 'heroicon-m-minus-circle',
                        'expire'             => 'heroicon-m-clock',
                        default              => 'heroicon-m-circle-stack',
                    }),

                // Points (+ / -)
                TextColumn::make('points')
                    ->label(__('Points'))
                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? '+' : '') . number_format($state))
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),

                // EGP value
                TextColumn::make('egp_value')
                    ->label(__('EGP Value'))
                    ->state(function (PointTransaction $record) use ($rate): string {
                        return number_format(abs($record->points) * $rate, 2) . ' EGP';
                    })
                    ->color('gray'),

                // Balance after
                TextColumn::make('balance_after')
                    ->label(__('Balance After'))
                    ->formatStateUsing(fn (int $state): string => number_format($state) . ' pts')
                    ->badge()
                    ->color('info'),

                // Note
                TextColumn::make('note')
                    ->label(__('Note'))
                    ->limit(50)
                    ->tooltip(fn (PointTransaction $record): ?string => $record->note)
                    ->placeholder('—'),

                // Admin who created it
                TextColumn::make('createdByAdmin.name')
                    ->label(__('By Admin'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Date
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options($typeLabels),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
