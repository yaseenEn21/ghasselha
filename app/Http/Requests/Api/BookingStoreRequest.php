<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'car_id' => ['required','integer','exists:cars,id'],
            'address_id' => ['required','integer','exists:addresses,id'],
            'service_id' => ['required','integer','exists:services,id'],

            'date' => ['required','date_format:d-m-Y'],
            'time' => ['required','regex:/^\d{2}:\d{2}$/'],

            // اختيار موظف اختياري (للتست)
            'employee_id' => ['nullable','integer','exists:employees,id'],

            'package_subscription_id' => ['nullable','integer','exists:package_subscriptions,id'],

            'products' => ['nullable','array'],
            'products.*.product_id' => ['required_with:products','integer','exists:products,id'],
            'products.*.qty' => ['required_with:products','integer','min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date_format' => 'صيغة التاريخ يجب أن تكون d-m-Y.',
            'time.regex' => 'صيغة الوقت يجب أن تكون HH:MM.',
        ];
    }
}