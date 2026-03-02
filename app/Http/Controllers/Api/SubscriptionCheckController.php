<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\Api\SubscriptionResource;
use App\Http\Requests\Api\SubscriptionCheckRequest;
use App\Models\FamilyMember;

class SubscriptionCheckController extends Controller
{
    public function check(SubscriptionCheckRequest $request)
    {
        $nationalId = $request->national_id;
        $membershipNumber = $request->membership_card_number;

        // 1. Try finding a primary User
        $user = User::where('national_id', $nationalId)
            ->where('membership_card_number', $membershipNumber)
            ->first();

        $subscriptions = collect();
        $memberName = '';

        if ($user) {
            $memberName = $user->name;
            $subscriptions = $user->subscriptions()
                ->whereHas('payments', function ($query) {
                    $query->where('status', 'completed');
                })
                ->with('subscriptionPlan')
                ->latest()
                ->get();
        } else {
            // 2. Try finding a FamilyMember with this National ID whose parent User has this Membership Number
            $familyMember = FamilyMember::where('national_id', $nationalId)
                ->whereHas('user', function ($query) use ($membershipNumber) {
                    $query->where('membership_card_number', $membershipNumber);
                })
                ->with('user')
                ->first();

            if ($familyMember) {
                $memberName = $familyMember->name . ' (Family Member of ' . $familyMember->user->name . ')';
                $subscriptions = $familyMember->user->subscriptions()
                    ->whereHas('payments', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->with('subscriptionPlan')
                    ->latest()
                    ->get();
            }
        }

        if ($subscriptions->isEmpty() && empty($memberName)) {
            return response()->json([
                'message' => 'Member not found with these credentials',
            ], 404);
        }

        return response()->json([
            'member_name' => $memberName,
            'subscriptions' => SubscriptionResource::collection($subscriptions),
        ]);
    }
}
