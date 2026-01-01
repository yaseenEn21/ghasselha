<?php

namespace App\Http\Requests\Api;

use App\Models\VehicleModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // توحيد الأحرف لتجنب duplicates بسبب اختلاف case
        if ($this->has('plate_letters')) {
            $this->merge([
                'plate_letters' => strtoupper((string) $this->input('plate_letters')),
            ]);
        }
    }

    public function rules(): array
    {
        $carId = $this->route('car')?->id; // Route Model Binding
        $userId = $this->user()?->id;

        return [
            'vehicle_make_id'  => ['sometimes', 'required', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => ['sometimes', 'required', 'exists:vehicle_models,id'],

            'color' => ['sometimes', 'nullable', 'string', Rule::in([
                'red','silver','white','black',
                'brown','orange','purple','gold',
                'green','blue','yellow','beige',
            ])],

            'plate_number' => ['sometimes', 'required', 'string', 'regex:/^\d{1,4}$/'],

            'plate_letters' => [
                'sometimes',
                'required',
                'string',
                'regex:/^[A-Za-z\x{0600}-\x{06FF}]{1,3}$/u',

                // unique مركّب (user_id + plate_number + plate_letters) مع ignore للسيارة الحالية
                Rule::unique('cars', 'plate_letters')
                    ->ignore($carId)
                    ->where(fn ($q) => $q
                        ->where('user_id', $userId)
                        ->where('plate_number', $this->input('plate_number'))
                    ),
            ],

            'plate_letters_ar' => ['sometimes', 'nullable', 'string', 'regex:/^[\x{0600}-\x{06FF}]{1,3}$/u'],

            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // لو المستخدم بعث make أو model أو الاثنين، نتحقق أنهم متطابقين

            $makeId  = $this->input('vehicle_make_id');
            $modelId = $this->input('vehicle_model_id');

            // إذا واحد منهم مفقود: حاول نجيبه من السيارة الحالية
            $car = $this->route('car');
            if ($car) {
                $makeId  = $makeId  ?? $car->vehicle_make_id;
                $modelId = $modelId ?? $car->vehicle_model_id;
            }

            if ($makeId && $modelId) {
                $ok = VehicleModel::query()
                    ->where('id', $modelId)
                    ->where('vehicle_make_id', $makeId)
                    ->exists();

                if (!$ok) {
                    $v->errors()->add('vehicle_model_id', 'Model does not belong to selected make.');
                }
            }
        });
    }
}