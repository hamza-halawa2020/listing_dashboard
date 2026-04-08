<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAuthenticated = Auth::guard('sanctum')->check();

        return [
            'review' => ['required', 'string', 'min:10', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'guest_name' => $isAuthenticated
                ? ['nullable', 'string', 'max:255']
                : ['required', 'string', 'max:255'],
            'guest_email' => $isAuthenticated
                ? ['nullable', 'email', 'max:255']
                : ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'review.required' => 'التقييم مطلوب',
            'review.min' => 'التقييم يجب أن يكون 10 أحرف على الأقل',
            'rating.required' => 'التقييم (النجوم) مطلوب',
            'rating.min' => 'التقييم يجب أن يكون من 1 إلى 5 نجوم',
            'rating.max' => 'التقييم يجب أن يكون من 1 إلى 5 نجوم',
            'guest_name.required' => 'الاسم مطلوب عند عدم تسجيل الدخول',
            'guest_email.email' => 'البريد الإلكتروني غير صحيح',
        ];
    }
}
