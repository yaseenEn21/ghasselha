<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsedCouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $coupon = $this->relationLoaded('coupon') ? $this->coupon : null;
        $promotion = $coupon?->relationLoaded('promotion') ? $coupon->promotion : null;

        // ✅ الخصم صار من coupon
        $discountText = null;
        if ($coupon?->discount_type === 'percent') {
            $v = $coupon->discount_value !== null ? (string) $coupon->discount_value : '0';
            $discountText = rtrim(rtrim($v, '0'), '.') . '%';
        } elseif ($coupon?->discount_type === 'fixed') {
            $v = $coupon->discount_value !== null ? (string) $coupon->discount_value : '0';
            $discountText = '-' . rtrim(rtrim($v, '0'), '.') . ' SAR';
        }

        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,

            'status' => $this->status, // applied|voided
            'status_label' => __('coupons.status.' . ($this->status === 'voided' ? 'voided' : 'used')),

            'discount_amount' => (string) $this->discount_amount,
            'used_at' => $this->applied_at?->toDateTimeString(),

            'coupon' => $coupon ? [
                'id' => $coupon->id,
                'code' => $coupon->code,

                // ✅ معلومات مهمة للـ UI
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value !== null ? (string) $coupon->discount_value : null,
                'discount_text' => $discountText,

                'applies_to' => $coupon->applies_to,
                'apply_all_services' => (bool) $coupon->apply_all_services,
                'apply_all_packages' => (bool) $coupon->apply_all_packages,

                'starts_at' => $coupon->starts_at?->toDateString(),
                'ends_at' => $coupon->ends_at?->toDateString(),

                'min_invoice_total' => $coupon->min_invoice_total !== null ? (string) $coupon->min_invoice_total : null,
                'max_discount' => $coupon->max_discount !== null ? (string) $coupon->max_discount : null,
            ] : null,

            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'name' => i18n($promotion->name),
                'description' => $promotion->description ? i18n($promotion->description) : null,
                'starts_at' => $promotion->starts_at?->toDateString(),
                'ends_at' => $promotion->ends_at?->toDateString(),
            ] : null,
        ];
    }
}