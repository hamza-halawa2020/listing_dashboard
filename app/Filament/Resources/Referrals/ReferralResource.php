<?php

namespace App\Filament\Resources\Referrals;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\Referrals\Pages\ManageReferrals;
use App\Models\Referral;
use BackedEnum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralResource extends AuthorizedResource
{
    protected static ?string $model = Referral::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('Referral');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Referrals');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Referral & Rewards');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'referrer',
                'referredUser',
                'qualifiedPayment',
                'qualifiedSubscription',
            ]);
    }

    public static function table(Table $table): Table
    {
        $statusLabels = Referral::statusLabels();
        $triggerLabels = Referral::triggerLabels();
        $rewardTypeLabels = Referral::rewardTypeLabels();

        return $table
            ->columns([
                TextColumn::make('referrer.name')
                    ->label(__('Referrer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referredUser.name')
                    ->label(__('New User'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referral_code_used')
                    ->label(__('Referral Code'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Referral::STATUS_PENDING => 'warning',
                        Referral::STATUS_QUALIFIED => 'info',
                        Referral::STATUS_REWARDED => 'success',
                        Referral::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => $statusLabels[$state] ?? $state),
                TextColumn::make('trigger_type')
                    ->label(__('Trigger'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $triggerLabels[$state] ?? $state),
                TextColumn::make('referrer_points_awarded')
                    ->label(__('Referrer Points'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('referee_reward_type')
                    ->label(__('New User Reward'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $rewardTypeLabels[$state] ?? $state),
                TextColumn::make('referee_reward_value')
                    ->label(__('Reward Value'))
                    ->formatStateUsing(function (Referral $record): string {
                        return match ($record->referee_reward_type) {
                            Referral::REWARD_POINTS => (string) (int) round((float) $record->referee_reward_value) . ' ' . __('points'),
                            Referral::REWARD_FIXED_DISCOUNT => number_format((float) $record->referee_reward_value, 2) . ' ' . __('EGP'),
                            Referral::REWARD_PERCENT_DISCOUNT => number_format((float) $record->referee_reward_value, 2) . '%',
                            default => '-',
                        };
                    }),
                TextColumn::make('referee_discount_amount_applied')
                    ->label(__('Applied Discount'))
                    ->money('egp')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('qualifiedPayment.id')
                    ->label(__('Qualified Payment #'))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('rewarded_at')
                    ->label(__('Rewarded At'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label(__('Notes'))
                    ->placeholder('-')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options($statusLabels),
                SelectFilter::make('trigger_type')
                    ->label(__('Trigger'))
                    ->options($triggerLabels),
                SelectFilter::make('referee_reward_type')
                    ->label(__('New User Reward'))
                    ->options($rewardTypeLabels),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReferrals::route('/'),
        ];
    }
}
