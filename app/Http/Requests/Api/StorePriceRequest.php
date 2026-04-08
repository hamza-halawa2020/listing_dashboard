<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'company_type' => ['required', 'in:individual,company,organization'],
            'employee_count' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'services_needed' => ['required', 'string', 'min:10', 'max:2000'],
            'additional_requirements' => ['nullable', 'string', 'max:2000'],
            'budget_range' => ['nullable', 'in:under_1000,1000_5000,5000_10000,10000_25000,over_25000'],
            'timeline' => ['nullable', 'in:urgent,week,month,quarter,flexible'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_person.required' => 'اسم الشخص المسؤول مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'phone.required' => 'رقم الهاتف مطلوب',
            'company_type.required' => 'نوع الشركة مطلوب',
            'company_type.in' => 'نوع الشركة غير صحيح',
            'employee_count.integer' => 'عدد الموظفين يجب أن يكون رقماً',
            'employee_count.min' => 'عدد الموظفين يجب أن يكون على الأقل 1',
            'employee_count.max' => 'عدد الموظفين يجب أن يكون أقل من 10000',
            'services_needed.required' => 'الخدمات المطلوبة مطلوبة',
            'services_needed.min' => 'الخدمات المطلوبة يجب أن تكون 10 أحرف على الأقل',
            'services_needed.max' => 'الخدمات المطلوبة يجب أن تكون أقل من 2000 حرف',
            'additional_requirements.max' => 'المتطلبات الإضافية يجب أن تكون أقل من 2000 حرف',
            'budget_range.in' => 'نطاق الميزانية غير صحيح',
            'timeline.in' => 'الجدول الزمني غير صحيح',
        ];
    }
}
