<?php

namespace App\Filament\Widgets;

use App\Models\PointTransaction;
use App\Models\Referral;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReferralOverviewStats extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalReferrals = Referral::count();
        $rewardedReferrals = Referral::where('status', Referral::STATUS_REWARDED)->count();
        $pendingReferrals = Referral::where('status', Referral::STATUS_PENDING)->count();

        $totalPointsAwarded = PointTransaction::whereIn('type', ['referral_bonus', 'referee_bonus'])->sum('points');

        $eligibleReferrers = User::whereHas('payments', fn ($q) => $q->where('status', 'completed'))->count();

        return [
            Stat::make(__('Total Referrals'), $totalReferrals)
                ->color('primary')
                ->icon('heroicon-o-megaphone'),
            Stat::make(__('Rewarded Referrals'), $rewardedReferrals)
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make(__('Pending Referrals'), $pendingReferrals)
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make(__('Eligible Referrers'), $eligibleReferrers)
                ->description(__('Users with at least one completed payment'))
                ->color('info')
                ->icon('heroicon-o-users'),
            Stat::make(__('Total Points Awarded'), number_format($totalPointsAwarded))
                ->color('success')
                ->icon('heroicon-o-star'),
        ];
    }
}
