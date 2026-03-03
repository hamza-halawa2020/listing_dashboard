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
        $nationalId = (int)$request->national_id;
        $membershipNumber = (int)$request->membership_card_number;

        // dd($nationalId, $membershipNumber);

        // 1. Try finding a primary User
        $user = User::where('national_id', $nationalId)->first();

        $subscriptions = collect();
        $memberName = '';

        if ($user) {
            $memberName = $user->name;
            $subscriptions = $user->subscriptions()
                ->where('membership_card_number', $membershipNumber)
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
                    $query->whereHas('subscriptions', function ($subscriptionQuery) use ($membershipNumber) {
                        $subscriptionQuery->where('membership_card_number', $membershipNumber);
                    });
                })
                ->with('user')
                ->first();

            if ($familyMember) {
                $memberName = $familyMember->name . ' (Family Member of ' . $familyMember->user->name . ')';
                $subscriptions = $familyMember->user->subscriptions()
                    ->where('membership_card_number', $membershipNumber)
                    ->whereHas('payments', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->with('subscriptionPlan')
                    ->latest()
                    ->get();
            }
        }

        if ($subscriptions->isEmpty()) {
            return response()->json([
                // 'message' => 'No matching paid subscription found with these credentials',
            ], 404);
        }

        return response()->json([
            'member_name' => $memberName,
            'subscriptions' => SubscriptionResource::collection($subscriptions),
        ]);
    }
}
