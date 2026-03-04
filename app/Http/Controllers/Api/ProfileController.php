<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFamilyMemberRequest;
use App\Http\Requests\Api\UpdateFamilyMemberRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\Api\FamilyMemberResource;
use App\Http\Resources\Api\UserResource;
use App\Support\FamilyMemberSubscription;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'user' => new UserResource(
                $this->loadProfileRelations($request->user()),
            ),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if (array_key_exists('password', $data) && blank($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data)->save();

        return response()->json([
            'user' => new UserResource(
                $this->loadProfileRelations($user->fresh()),
            ),
        ]);
    }

    public function storeFamilyMember(StoreFamilyMemberRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        FamilyMemberSubscription::ensureValidForUser(
            $user,
            $data['subscription_id'],
        );

        $familyMember = $user->familyMembers()->create($data);

        return response()->json([
            'family_member' => new FamilyMemberResource($familyMember->load('subscription.subscriptionPlan')),
            'user' => new UserResource(
                $this->loadProfileRelations($user->fresh()),
            ),
        ], 201);
    }

    public function updateFamilyMember(UpdateFamilyMemberRequest $request, int $id)
    {
        $user = $request->user();
        $familyMember = $user->familyMembers()->findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('subscription_id', $data)) {
            FamilyMemberSubscription::ensureValidForUser(
                $user,
                $data['subscription_id'],
                $familyMember,
            );
        }

        $familyMember->fill($data)->save();

        return response()->json([
            'family_member' => new FamilyMemberResource($familyMember->fresh()->load('subscription.subscriptionPlan')),
            'user' => new UserResource(
                $this->loadProfileRelations($user->fresh()),
            ),
        ]);
    }

    private function loadProfileRelations(User $user): User
    {
        return $user->load([
            'location',
            'familyMembers.subscription.subscriptionPlan',
            'payments',
            'subscriptions' => fn ($query) => $query
                ->with(['user', 'subscriptionPlan', 'familyMembers', 'payments'])
                ->latest(),
        ]);
    }
}
