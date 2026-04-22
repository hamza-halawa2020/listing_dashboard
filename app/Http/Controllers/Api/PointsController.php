<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointSetting;
use App\Models\PointTransaction;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();

        $transactions = PointTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $signupBonus = $transactions
            ->where('type', 'signup_bonus')
            ->sum('points');

        $subscriptionBonus = $transactions
            ->where('type', 'subscription_bonus')
            ->sum('points');

        $referralBonus = $transactions
            ->where('type', 'referral_bonus')
            ->sum('points');

        $refereeBonus = $transactions
            ->where('type', 'referee_bonus')
            ->sum('points');

        $redeemed = $transactions
            ->whereIn('type', ['redeem'])
            ->sum('points'); // negative values

        $rate = PointSetting::getCurrentRate();

        return response()->json([
            'balance'            => (int) $user->points_balance,
            'balance_egp'        => round($user->points_balance * $rate, 2),
            'rate'               => $rate,
            'earned' => [
                'signup_bonus'       => (int) $signupBonus,
                'subscription_bonus' => (int) $subscriptionBonus,
                'referral_bonus'     => (int) $referralBonus,
                'referee_bonus'      => (int) $refereeBonus,
                'total'              => (int) ($signupBonus + $subscriptionBonus + $referralBonus + $refereeBonus),
            ],
            'redeemed'           => (int) abs($redeemed),
            'recent_transactions' => $transactions->take(10)->map(fn ($t) => [
                'type'        => $t->type,
                'points'      => $t->points,
                'balance_after' => $t->balance_after,
                'note'        => $t->note,
                'created_at'  => $t->created_at->format('Y-m-d H:i:s'),
            ])->values(),
        ]);
    }
}
