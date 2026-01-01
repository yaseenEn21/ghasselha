<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['home','work','other'])],

            'country' => ['nullable','string','max:100'],
            'city' => ['nullable','string','max:100'],
            'area' => ['nullable','string','max:150'],
            'address_line' => ['nullable','string','max:255'],

            'building_name' => ['nullable','string','max:150'],
            'building_number' => ['nullable','string','max:50'],
            'landmark' => ['nullable','string','max:500'],

            // ✅ required
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],

            'is_default' => ['nullable','boolean'],
        ];
    }
}
