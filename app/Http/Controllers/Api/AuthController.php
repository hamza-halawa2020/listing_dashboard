<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'national_id' => $request->national_id,
            // 'membership_card_number' => $request->membership_card_number,
            'phone' => $request->phone,
            'role' => 'member', // Default role for API registration
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load([
            'location',
            'familyMembers',
            'payments',
            'subscriptions' => fn ($query) => $query
                ->with(['subscriptionPlan', 'payments'])
                ->latest(),
        ]);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {

        $user = User::where('national_id', $request->national_id)
                    // ->where('membership_card_number', $request->membership_card_number)
                    ->where('role', '!=', 'admin')
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'national_id' => ['The provided credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load([
            'location',
            'familyMembers',
            'payments',
            'subscriptions' => fn ($query) => $query
                ->with(['subscriptionPlan', 'payments'])
                ->latest(),
        ]);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }
}
