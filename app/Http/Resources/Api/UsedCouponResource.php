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

        $discountText = null;
        if ($promotion?->discount_type === 'percent')
            $discountText = rtrim(rtrim((string) $promotion->discount_value, '0'), '.') . '%';
        if ($promotion?->discount_type === 'fixed')
            $discountText = '-' . rtrim(rtrim((string) $promotion->discount_value, '0'), '.') . ' SAR';

        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'status' => $this->status,
            'status_label' => __('coupons.status.used'),
            
            'discount_amount' => (string) $this->discount_amount,
            'used_at' => $this->applied_at?->toDateTimeString(),

            'coupon' => $coupon ? [
                'id' => $coupon->id,
                'code' => $coupon->code,
            ] : null,

            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'name' => i18n($promotion->name),
                'discount_text' => $discountText,
                'applies_to' => $promotion->applies_to,
            ] : null,
        ];
    }
}
