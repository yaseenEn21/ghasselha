<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardCarStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vehicle_make_id' => ['required', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => ['required', 'exists:vehicle_models,id'],

            'color' => ['nullable','string','in:red,silver,white,black,brown,orange,purple,gold,green,blue,yellow,beige'],

            'plate_number' => ['required','string','regex:/^\d{1,4}$/'],
            'plate_letters' => ['required','string','regex:/^[A-Za-z\x{0600}-\x{06FF}]{1,3}$/u'],
            'plate_letters_ar' => ['nullable','string','regex:/^[\x{0600}-\x{06FF}]{1,3}$/u'],

            'is_default' => ['nullable','boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $makeId = $this->input('vehicle_make_id');
            $modelId = $this->input('vehicle_model_id');

            $ok = \App\Models\VehicleModel::query()
                ->where('id', $modelId)
                ->where('vehicle_make_id', $makeId)
                ->exists();

            if (!$ok) {
                $v->errors()->add('vehicle_model_id', __('bookings.car_model_not_belongs'));
            }
        });
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'plate_letters' => strtoupper((string) $this->plate_letters),
        ]);
    }
}