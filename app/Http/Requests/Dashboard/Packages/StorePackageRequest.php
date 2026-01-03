<?php

// app/Http/Requests/Dashboard/Packages/StorePackageRequest.php

namespace App\Http\Requests\Dashboard\Packages;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],

            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],

            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],

            'validity_days' => ['required', 'integer', 'min:1'],
            'washes_count' => ['required', 'integer', 'min:1'],

            'sort_order' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],

            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'image_remove' => ['nullable', 'boolean'],

            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],

            'service_sort_orders' => ['nullable', 'array'],
            'service_sort_orders.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}