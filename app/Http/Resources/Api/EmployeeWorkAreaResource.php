<?php
namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeWorkAreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'polygon' => $this->polygon,
            'bbox' => [
                'min_lat' => (string) $this->min_lat,
                'max_lat' => (string) $this->max_lat,
                'min_lng' => (string) $this->min_lng,
                'max_lng' => (string) $this->max_lng,
            ],
            'is_active' => (bool) $this->is_active,
        ];
    }
}