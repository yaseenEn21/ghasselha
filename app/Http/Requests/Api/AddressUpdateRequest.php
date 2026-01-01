<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['sometimes','required', Rule::in(['home','work','other'])],

            'country' => ['sometimes','nullable','string','max:100'],
            'city' => ['sometimes','nullable','string','max:100'],
            'area' => ['sometimes','nullable','string','max:150'],
            'address_line' => ['sometimes','nullable','string','max:255'],

            'building_name' => ['sometimes','nullable','string','max:150'],
            'building_number' => ['sometimes','nullable','string','max:50'],
            'landmark' => ['sometimes','nullable','string','max:500'],

            'lat' => ['sometimes','required','numeric','between:-90,90'],
            'lng' => ['sometimes','required','numeric','between:-180,180'],

            'is_default' => ['sometimes','boolean'],
        ];
    }
}
