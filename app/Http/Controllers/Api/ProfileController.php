<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFamilyMemberRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\Api\FamilyMemberResource;
use App\Http\Resources\Api\UserResource;
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

        $familyMember = $user->familyMembers()->create($request->validated());

        return response()->json([
            'family_member' => new FamilyMemberResource($familyMember),
            'user' => new UserResource(
                $this->loadProfileRelations($user->fresh()),
            ),
        ], 201);
    }

    private function loadProfileRelations(User $user): User
    {
        return $user->load([
            'location',
            'familyMembers',
            'payments',
            'subscriptions' => fn ($query) => $query
                ->with(['subscriptionPlan', 'payments'])
                ->latest(),
        ]);
    }
}
