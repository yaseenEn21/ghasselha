<?php
namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => $this->relationLoaded('user') ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'mobile' => $this->user->mobile,
            ] : null,

            'is_active' => (bool) $this->is_active,

            'services' => $this->relationLoaded('services')
                ? $this->services->map(fn($s) => [
                    'id' => $s->id,
                    'name' => i18n($s->name),
                ])->values()
                : null,

            'work_area' => $this->relationLoaded('workArea') && $this->workArea
                ? new EmployeeWorkAreaResource($this->workArea)
                : null,
        ];
    }
}