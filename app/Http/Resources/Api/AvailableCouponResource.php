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

        $promoStart  = $promotion?->starts_at?->toDateString();
        $couponStart = $this->starts_at?->toDateString();

        $promoEnd  = $promotion?->ends_at?->toDateString();
        $couponEnd = $this->ends_at?->toDateString();

        // ✅ تاريخ البداية النهائي = الأكبر (لازم يكون الطرفين بدأوا)
        $startsAt = null;
        if ($promoStart && $couponStart) $startsAt = max($promoStart, $couponStart);
        else $startsAt = $promoStart ?: $couponStart;

        // ✅ تاريخ الانتهاء النهائي = الأقل
        $endsAt = null;
        if ($promoEnd && $couponEnd) $endsAt = min($promoEnd, $couponEnd);
        else $endsAt = $promoEnd ?: $couponEnd;

        // status
        $status = 'available';
        if ($startsAt && $startsAt > $today) $status = 'upcoming';
        if ($endsAt && $endsAt < $today) $status = 'expired';

        // ✅ الخصم من الكوبون
        $discountType  = $this->discount_type;
        $discountValue = $this->discount_value;

        $discountText = null;
        if ($discountType === 'percent') {
            $discountText = rtrim(rtrim((string) $discountValue, '0'), '.') . '%';
        } elseif ($discountType === 'fixed') {
            $discountText = '-' . rtrim(rtrim((string) $discountValue, '0'), '.') . ' SAR';
        }

        $userUsed = (int) ($this->user_used_count ?? 0);
        $isUsed = $userUsed > 0;

        $limitPerUser = $this->usage_limit_per_user !== null ? (int) $this->usage_limit_per_user : null;
        $limitTotal   = $this->usage_limit_total !== null ? (int) $this->usage_limit_total : null;

        $remaining = 0;
        if ($limitPerUser !== null) {
            $remaining = max(0, $limitPerUser - $userUsed);
        }

        // ✅ can_use: status=available + limits + is_active (coupon + promotion)
        $canUse = ($status === 'available');

        if ($canUse && !$this->is_active) $canUse = false;
        if ($canUse && $promotion && !$promotion->is_active) $canUse = false;

        if ($canUse && $limitPerUser !== null && $userUsed >= $limitPerUser) {
            $canUse = false;
        }

        // total limit عبر used_count
        $usedCount = (int) ($this->used_count ?? 0);
        if ($canUse && $limitTotal !== null && $usedCount >= $limitTotal) {
            $canUse = false;
        }

        return [
            'id' => $this->id,
            'code' => $this->code,

            'status' => $status,
            'status_label' => __('coupons.status.' . $status),

            'starts_at' => $startsAt,
            'ends_at' => $endsAt,

            'discount_type' => $discountType,
            'discount_value' => $discountValue !== null ? (string) $discountValue : null,
            'discount_text' => $discountText,

            'max_discount' => $this->max_discount !== null ? (string) $this->max_discount : null,

            'min_invoice_total' => $this->min_invoice_total !== null ? (string) $this->min_invoice_total : null,
            'usage_limit_total' => $this->usage_limit_total,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'used_count' => $usedCount,

            'applies_to' => $this->applies_to,
            'apply_all_services' => (bool) $this->apply_all_services,
            'apply_all_packages' => (bool) $this->apply_all_packages,

            'user_used_count' => $userUsed,
            'is_used' => $isUsed,
            'can_use' => $canUse,
            'remaining_uses_for_user' => $remaining,

            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'name' => i18n($promotion->name),
                'description' => $promotion->description ? i18n($promotion->description) : null,
                'starts_at' => $promotion->starts_at?->toDateString(),
                'ends_at' => $promotion->ends_at?->toDateString(),
                'is_active' => (bool) $promotion->is_active,
            ] : null,
        ];
    }
}