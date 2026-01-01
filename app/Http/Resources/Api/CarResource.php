<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'vehicle_make_id' => $this->vehicle_make_id,
            'vehicle_model_id' => $this->vehicle_model_id,

            'make_name' => $this->relationLoaded('make') ? i18n($this->make->name) : null,
            'model_name' => $this->relationLoaded('model') ? i18n($this->model->name) : null,

            'color' => $this->color,
            'color_label' => $this->color ? __('colors.' . $this->color) : null,
            'color_hex' => car_color_hex($this->color),

            'plate_number' => $this->plate_number,
            'plate_letters' => $this->plate_letters,
            'plate_letters_ar' => $this->plate_letters_ar,
            'plate_number_ar' => $this->plate_number,
            'plate_full' => $this->plate_full,

            'is_default' => (bool) $this->is_default,
        ];
    }

}