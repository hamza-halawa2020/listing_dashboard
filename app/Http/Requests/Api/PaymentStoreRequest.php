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
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,fawry,vodafone_cash,instapay',
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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
