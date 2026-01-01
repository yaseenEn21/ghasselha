<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeWeeklyIntervalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day,
            'day_label' => __('days.' . $this->day),
            'type' => $this->type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_active' => (bool) $this->is_active,
        ];
    }
}