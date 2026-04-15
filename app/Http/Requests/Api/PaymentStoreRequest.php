<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => 'required|exists:subscription_plans,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,fawry,vodafone_cash,instapay,points',
            'national_id' => 'nullable|string|max:20|min:14',
            'transaction_reference' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'delivery_required' => 'nullable|boolean',
            'location_id' => 'nullable|required_if:delivery_required,1|exists:locations,id',
            'delivery_name' => 'nullable|required_if:delivery_required,1|string|max:255',
            'delivery_phone' => 'nullable|required_if:delivery_required,1|string|max:255',
            'delivery_address' => 'nullable|required_if:delivery_required,1|string',
            'shipping_cost' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $userHasNationalId = filled($this->user()?->national_id);
            $requestHasNationalId = filled($this->national_id);

            if ($userHasNationalId || $requestHasNationalId) {
                return;
            }

            $validator->errors()->add(
                'national_id',
                __('You must add your national ID before subscribing to any plan.'),
            );
        });

        // Validate points redemption
        if ($this->payment_method === 'points') {
            $validator->after(function ($validator): void {
                $plan = \App\Models\SubscriptionPlan::find($this->plan_id);

                if (! $plan || !$plan->isAvailableForPoints()) {
                    $validator->errors()->add('payment_method', __('This plan is not available for points redemption.'));
                    return;
                }

                $userPoints = (int) ($this->user()?->points_balance ?? 0);
                $pointsRequired = $plan->points_price_calculated;

                if ($userPoints < $pointsRequired) {
                    $validator->errors()->add('payment_method', __('You do not have enough points to redeem this plan. Required: :required, Available: :available.', [
                        'required' => $pointsRequired,
                        'available' => $userPoints,
                    ]));
                }
            });
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => __('Validation error'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
