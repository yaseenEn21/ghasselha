<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => i18n($this->name),
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'services_count' => $this->when(isset($this->services_count), $this->services_count),
        ];
    }
}