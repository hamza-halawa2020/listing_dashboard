<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CardActivationRequest;
use App\Http\Resources\Api\MembershipCardResource;
use App\Models\MembershipCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CardActivationController extends ApiController
{
    /**
     * POST /card-activation
     *
     * User submits their card number + national ID to request activation.
     * The card must exist, be unassigned (or previously rejected), and NOT already belong to another user.
     */
    public function activate(CardActivationRequest $request): MembershipCardResource|JsonResponse
    {
        $validated = $request->validated();
        $user      = auth()->user();

        /** @var MembershipCard|null $card */
        $card = MembershipCard::where('card_number', $validated['card_number'])->first();

        if (! $card) {
            throw ValidationException::withMessages([
                'card_number' => [__('Card not found. Please check the card number.')],
            ]);
        }

        // Card must be in a state that allows activation requests
        $allowedStatuses = [
            MembershipCard::STATUS_UNASSIGNED,
            MembershipCard::STATUS_REJECTED,
        ];

        if (! in_array($card->status, $allowedStatuses, true)) {
            $label = MembershipCard::statusLabel($card->status);
            throw ValidationException::withMessages([
                'card_number' => [__('This card cannot be activated. Current status: :status.', ['status' => $label])],
            ]);
        }

        // Safety check: card already assigned to a different user
        if ($card->user_id && $card->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'card_number' => [__('This card belongs to another user.')],
            ]);
        }

        $card->requestActivation($validated['national_id']);

        return new MembershipCardResource($card->load('subscriptionPlan'));
    }

    /**
     * GET /my-cards
     *
     * Returns all membership cards linked to or requested by the authenticated user.
     */
    public function myCards(Request $request): JsonResponse
    {
        $user = auth()->user();

        $cards = MembershipCard::with(['subscriptionPlan'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                // Also include cards the user requested activation for (pending/rejected, no user yet)
                if (filled($user->national_id)) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->whereNull('user_id')
                          ->where('national_id', $user->national_id);
                    });
                }
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => MembershipCardResource::collection($cards),
        ]);
    }
}
