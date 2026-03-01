<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Http\Resources\Api\PaymentResource;
use App\Http\Requests\Api\PaymentStoreRequest;
use Carbon\Carbon;

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

        // 1. Create a pending/active subscription record
        $subscription = Subscription::create([
            'user_id' => auth()->id(),
            'subscription_plan_id' => $plan->id,
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addDays($plan->duration_days),
            'status' => 'active', // Assuming it's active once paid, or add 'pending' to Enum
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->transaction_reference,
            'notes' => $request->notes,
        ]);

        // 2. Prepare data for the payment record
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['subscription_id'] = $subscription->id;
        $data['amount'] = $plan->price;
        $data['status'] = 'pending';

        // 3. Handle attachment storage
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('payments', 'public');
            $data['attachment'] = $path;
        } else {
            unset($data['attachment']);
        }

        $payment = Payment::create($data);

        return new PaymentResource($payment);
    }
}
