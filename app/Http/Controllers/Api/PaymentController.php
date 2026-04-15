<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Location;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Http\Resources\Api\PaymentResource;
use App\Http\Requests\Api\PaymentStoreRequest;
use App\Services\ReferralService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends ApiController
{
    public function __construct()
    {
        $this->model = Payment::class;
        $this->resource = PaymentResource::class;
    }

    public function store(PaymentStoreRequest $request, ReferralService $referralService)
    {
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $validated = $request->validated();
        $user = auth()->user();

        if (filled($validated['national_id']) && blank($user->national_id)) {
            $user->update(['national_id' => $validated['national_id']]);
            $user->refresh();
        }

        // Points redemption flow
        if ($validated['payment_method'] === 'points') {
            return $this->redeemWithPoints($plan, $user, $validated, $referralService);
        }

        $deliveryRequired = (bool) ($validated['delivery_required'] ?? false);
        $location = $deliveryRequired ? Location::findOrFail($validated['location_id']) : null;
        $shippingCost = $deliveryRequired ? (float) ($location?->shipping_cost ?? 0) : 0;
        $pricing = $referralService->applyRefereeDiscount($user, (float) $plan->price + $shippingCost, $plan);

        $payment = DB::transaction(function () use ($request, $validated, $plan, $location, $deliveryRequired, $shippingCost, $pricing, $user, $referralService) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays($plan->duration_days),
                'status' => 'active',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $data = [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'referral_id' => $pricing['referral']?->id,
                'location_id' => $deliveryRequired ? $location?->id : null,
                'amount' => $pricing['final_amount'],
                'original_amount' => $pricing['original_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'delivery_required' => $deliveryRequired,
                'delivery_name' => $deliveryRequired ? ($validated['delivery_name'] ?? null) : null,
                'delivery_phone' => $deliveryRequired ? ($validated['delivery_phone'] ?? null) : null,
                'delivery_address' => $deliveryRequired ? ($validated['delivery_address'] ?? null) : null,
                'shipping_cost' => $shippingCost,
            ];

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('payments', 'public');
            }

            $payment = Payment::create($data);

            $referralService->reserveDiscountForPayment($payment);

            return $payment;
        });

        return new PaymentResource($payment);
    }

    private function redeemWithPoints(SubscriptionPlan $plan, $user, array $validated, ReferralService $referralService): PaymentResource
    {
        $pointsRequired = $plan->points_price_calculated;
        $currentRate = \App\Models\PointSetting::getCurrentRate();
        $egpValue = $plan->getPointsEgpValue();

        $payment = DB::transaction(function () use ($plan, $user, $validated, $pointsRequired, $referralService, $currentRate, $egpValue) {
            // Deduct points atomically
            $referralService->addPoints(
                $user,
                -$pointsRequired,
                'redeem',
                null,
                null,
                __('Points redeemed for subscription plan: :plan', ['plan' => $plan->name]),
            );

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays($plan->duration_days),
                'status' => 'active',
                'payment_method' => 'points',
                'notes' => $validated['notes'] ?? null,
            ]);

            return Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => 0,
                'original_amount' => $egpValue,
                'discount_amount' => $egpValue,
                'payment_method' => 'points',
                'status' => 'completed',
                'paid_at' => now(),
                'notes' => __('Redeemed with :points points (Rate: :rate, Value: :value)', [
                    'points' => $pointsRequired,
                    'rate' => number_format($currentRate, 4) . ' EGP/point',
                    'value' => 'EGP ' . number_format($egpValue, 2),
                ]),
            ]);
        });

        return new PaymentResource($payment);
    }
}
