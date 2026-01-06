<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionCouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $promotion = $this->relationLoaded('promotion') ? $this->promotion : null;

        $meta = is_array($this->meta) ? $this->meta : (is_string($this->meta) ? (json_decode($this->meta, true) ?: []) : []);

        $serviceIds = array_values(array_filter(array_map('intval', (array)($meta['service_ids'] ?? []))));
        $packageIds = array_values(array_filter(array_map('intval', (array)($meta['package_ids'] ?? []))));

        $discountText = null;
        if ($this->discount_type === 'percent') {
            $discountText = rtrim(rtrim((string)$this->discount_value, '0'), '.') . '%';
        } elseif ($this->discount_type === 'fixed') {
            $discountText = '-' . rtrim(rtrim((string)$this->discount_value, '0'), '.') . ' SAR';
        }

        return [
            'id' => $this->id,
            'code' => $this->code,
            'is_active' => (bool)$this->is_active,

            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),

            // ✅ scope from coupon
            'applies_to' => $this->applies_to,
            'apply_all_services' => (bool)$this->apply_all_services,
            'apply_all_packages' => (bool)$this->apply_all_packages,
            'service_ids' => $serviceIds,
            'package_ids' => $packageIds,

            // ✅ discount from coupon
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value !== null ? (string)$this->discount_value : null,
            'discount_text' => $discountText,
            'max_discount' => $this->max_discount !== null ? (string)$this->max_discount : null,

            'min_invoice_total' => $this->min_invoice_total !== null ? (string)$this->min_invoice_total : null,
            'usage_limit_total' => $this->usage_limit_total,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'used_count' => (int)($this->used_count ?? 0),

            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'name' => i18n($promotion->name),
                'description' => $promotion->description ? i18n($promotion->description) : null,
                'starts_at' => $promotion->starts_at?->toDateString(),
                'ends_at' => $promotion->ends_at?->toDateString(),
                'is_active' => (bool)$promotion->is_active,
            ] : null,
        ];
    }
}