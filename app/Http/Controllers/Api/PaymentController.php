<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Location;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Http\Resources\Api\PaymentResource;
use App\Http\Requests\Api\PaymentStoreRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends ApiController
{
    public function __construct()
    {
        $this->model = Payment::class;
        $this->resource = PaymentResource::class;
    }

    public function store(PaymentStoreRequest $request)
    {
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $validated = $request->validated();
        $deliveryRequired = (bool) ($validated['delivery_required'] ?? false);
        $location = $deliveryRequired ? Location::findOrFail($validated['location_id']) : null;
        $shippingCost = $deliveryRequired ? (float) ($location?->shipping_cost ?? 0) : 0;
        $amount = (float) $plan->price + $shippingCost;

        $payment = DB::transaction(function () use ($request, $validated, $plan, $location, $deliveryRequired, $shippingCost, $amount) {
            $subscription = Subscription::create([
                'user_id' => auth()->id(),
                'subscription_plan_id' => $plan->id,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays($plan->duration_days),
                'status' => 'active',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $data = [
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
                'location_id' => $deliveryRequired ? $location?->id : null,
                'amount' => $amount,
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

            return Payment::create($data);
        });

        return new PaymentResource($payment);
    }
}
