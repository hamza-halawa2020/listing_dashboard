<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function show(Request $request, ReferralService $referralService)
    {
        $user = $request->user();

        $isEligible = $referralService->isUserEligibleToRefer($user);

        $sentReferrals = $user->sentReferrals()->with('referredUser')->get();

        $totalReferrals = $sentReferrals->count();
        $rewardedReferrals = $sentReferrals->where('status', 'rewarded')->count();
        $pendingReferrals = $sentReferrals->where('status', 'pending')->count();

        $pointsEarned = $user->pointTransactions()
            ->where('type', 'referral_bonus')
            ->sum('points');

        return response()->json([
            'referral_code' => $isEligible ? $user->referral_code : null,
            'is_eligible' => $isEligible,
            'stats' => [
                'total_referrals' => $totalReferrals,
                'rewarded_referrals' => $rewardedReferrals,
                'pending_referrals' => $pendingReferrals,
                'points_earned' => (int) $pointsEarned,
            ],
            'referrals' => $sentReferrals->map(fn ($r) => [
                'id' => $r->id,
                'referred_user_name' => $r->referredUser?->name,
                'status' => $r->status,
                'points_awarded' => $r->referrer_points_awarded,
                'rewarded_at' => $r->rewarded_at?->format('Y-m-d H:i:s'),
                'created_at' => $r->created_at->format('Y-m-d H:i:s'),
            ]),
        ]);
    }
}
