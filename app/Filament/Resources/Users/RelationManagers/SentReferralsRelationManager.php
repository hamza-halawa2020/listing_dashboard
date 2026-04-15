<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Referral;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SentReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'sentReferrals';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Referrals Sent');
    }

    public function table(Table $table): Table
    {
        $statusLabels = Referral::statusLabels();
        $rewardTypeLabels = Referral::rewardTypeLabels();

        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('referredUser.name')
                    ->label(__('New User'))
                    ->searchable(),
                TextColumn::make('referredUser.phone')
                    ->label(__('Phone'))
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
                TextColumn::make('referrer_points_awarded')
                    ->label(__('Points Awarded'))
                    ->numeric()
                    ->badge()
                    ->color('success'),
                TextColumn::make('referee_reward_type')
                    ->label(__('New User Reward'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $rewardTypeLabels[$state] ?? $state),
                TextColumn::make('referee_reward_value')
                    ->label(__('Reward Value'))
                    ->formatStateUsing(function (Referral $record): string {
                        return match ($record->referee_reward_type) {
                            Referral::REWARD_POINTS => (int) round((float) $record->referee_reward_value) . ' ' . __('points'),
                            Referral::REWARD_FIXED_DISCOUNT => number_format((float) $record->referee_reward_value, 2) . ' ' . __('EGP'),
                            Referral::REWARD_PERCENT_DISCOUNT => number_format((float) $record->referee_reward_value, 2) . '%',
                            default => '-',
                        };
                    }),
                TextColumn::make('rewarded_at')
                    ->label(__('Rewarded At'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
