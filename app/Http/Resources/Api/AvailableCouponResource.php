<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailableCouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $promotion = $this->relationLoaded('promotion') ? $this->promotion : null;

        $today = now()->toDateString();

        $promoEnd  = $promotion?->ends_at?->toDateString();
        $couponEnd = $this->ends_at?->toDateString();

        // تاريخ الانتهاء النهائي = الأقل
        $endsAt = null;
        if ($promoEnd && $couponEnd) $endsAt = min($promoEnd, $couponEnd);
        else $endsAt = $promoEnd ?: $couponEnd;

        // status الأساسي
        $status = 'available';
        if ($endsAt && $endsAt < $today) $status = 'expired';
        if ($this->starts_at && $this->starts_at->toDateString() > $today) $status = 'upcoming';

        $discountType  = $promotion?->discount_type;
        $discountValue = $promotion?->discount_value;

        $discountText = null;
        if ($discountType === 'percent') $discountText = rtrim(rtrim((string)$discountValue, '0'), '.') . '%';
        if ($discountType === 'fixed')   $discountText = '-' . rtrim(rtrim((string)$discountValue, '0'), '.') . ' SAR';

        // من الكونترولر: withCount redemptions as user_used_count
        $userUsed = (int) ($this->user_used_count ?? 0);
        $isUsed = $userUsed > 0;

        $limitPerUser = $this->usage_limit_per_user !== null ? (int) $this->usage_limit_per_user : null;

        $remaining = null;
        if ($limitPerUser !== null) {
            $remaining = max(0, $limitPerUser - $userUsed);
        }

        // can_use: لازم يكون status available + ما تجاوز limit
        $canUse = ($status === 'available');
        if ($canUse && $limitPerUser !== null && $userUsed >= $limitPerUser) {
            $canUse = false;
        }

        return [
            'id' => $this->id,
            'code' => $this->code,

            'status' => $status,
            'status_label' => __('coupons.status.'.$status),

            'ends_at' => $endsAt,

            'discount_type' => $discountType,
            'discount_value' => $discountValue !== null ? (string) $discountValue : null,
            'discount_text' => $discountText,

            'min_invoice_total' => $this->min_invoice_total !== null ? (string) $this->min_invoice_total : null,
            'usage_limit_total' => $this->usage_limit_total,
            'usage_limit_per_user' => $this->usage_limit_per_user,

            // ✅ UI helpers
            'user_used_count' => $userUsed,
            'is_used' => $isUsed,
            'can_use' => $canUse,
            'remaining_uses_for_user' => $remaining !== null ? $remaining : 0,

            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'name' => i18n($promotion->name),
                'description' => $promotion->description ? i18n($promotion->description) : null,
                'applies_to' => $promotion->applies_to,
            ] : null,
        ];
    }
}