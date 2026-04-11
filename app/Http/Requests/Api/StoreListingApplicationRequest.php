<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'location_id' => 'required|integer|exists:locations,id',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            'contact_name' => 'required|string|min:3|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|min:10|max:20',
            
            'phones' => 'nullable|array',
            'phones.*.number' => 'required_with:phones|string|max:20',
            'phones.*.type' => 'required_with:phones|in:Mobile,Landline,Fax',
            
            'working_hours' => 'nullable|array',
            'working_hours.*.day' => 'required_with:working_hours|string',
            'working_hours.*.is_closed' => 'required_with:working_hours|boolean',
            'working_hours.*.open_time' => 'nullable|date_format:H:i',
            'working_hours.*.close_time' => 'nullable|date_format:H:i',
            
            'links' => 'nullable|array',
            'links.*.url' => 'required_with:links|url',
            'links.*.title' => 'nullable|string|max:255',
            'links.*.type' => 'required_with:links|string|max:50',
            
            'images' => 'nullable|array',
            'images.*.image_path' => ['required_with:images', 'string', 'regex:/^data:image\/(jpeg|jpg|png|gif|webp|svg\+xml);base64,/i'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المشروع مطلوب',
            'category_id.required' => 'تصنيف المشروع مطلوب',
            'location_id.required' => 'الموقع مطلوب',
            'contact_name.required' => 'اسم المسؤول مطلوب',
            'contact_email.required' => 'البريد الإلكتروني مطلوب',
            'contact_phone.required' => 'رقم الهاتف مطلوب',
        ];
    }
}
