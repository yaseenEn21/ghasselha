<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionCouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $promotion = $this->relationLoaded('promotion') ? $this->promotion : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'is_active' => (bool) $this->is_active,

            'min_invoice_total' => $this->min_invoice_total !== null ? (string)$this->min_invoice_total : null,
            'usage_limit_total' => $this->usage_limit_total,
            'usage_limit_per_user' => $this->usage_limit_per_user,

            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'name' => i18n($promotion->name),
                'description' => $promotion->description ? i18n($promotion->description) : null,
                'applies_to' => $promotion->applies_to,
                'discount_type' => $promotion->discount_type,
                'discount_value' => (string)$promotion->discount_value,
                'max_discount' => $promotion->max_discount !== null ? (string)$promotion->max_discount : null,
            ] : null,
        ];
    }
}
