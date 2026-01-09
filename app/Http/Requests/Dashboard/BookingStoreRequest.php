<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],

            'booking_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],

            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'package_subscription_id' => ['nullable', 'integer', 'exists:package_subscriptions,id'],

            'products' => ['nullable', 'array'],
            'products.*.product_id' => ['required_with:products', 'integer', 'exists:products,id'],
            'products.*.qty' => ['required_with:products', 'integer', 'min:1'],

            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}