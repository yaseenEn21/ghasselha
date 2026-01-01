<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => __('addresses.' . $this->type),

            'country' => $this->country,
            'city' => $this->city,
            'area' => $this->area,
            'address_line' => $this->address_line,

            'building_name' => $this->building_name,
            'building_number' => $this->building_number,
            'landmark' => $this->landmark,

            'lat' => (string) $this->lat,
            'lng' => (string) $this->lng,

            'is_default' => (bool) $this->is_default,
        ];
    }
}