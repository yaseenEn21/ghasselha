<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PromotionCoupon;
use App\Models\PromotionCouponRedemption;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function preview(Invoice $invoice, User $user, string $code): array
    {
        $coupon = $this->findValidCoupon($code);
        $invoice->loadMissing('items');

        $promotion = $coupon->promotion->loadMissing(['services', 'packages']);

        $eligibleBase = $this->eligibleBase($invoice, $promotion);

        if ($eligibleBase <= 0) {
            return [
                'ok' => false,
                'message' => 'Coupon not applicable for this invoice.',
                'errors' => ['code' => ['Coupon not applicable for this invoice.']],
            ];
        }

        $gross = (float) $invoice->subtotal + (float) $invoice->tax; // قبل الخصم
        if ($coupon->min_invoice_total !== null && $gross < (float) $coupon->min_invoice_total) {
            return [
                'ok' => false,
                'message' => 'Invoice total is below coupon minimum.',
                'errors' => ['code' => ['Invoice total is below coupon minimum.']],
            ];
        }

        $discount = $this->calcDiscount($eligibleBase, $promotion, $coupon);

        // لا نخلي الخصم يتجاوز الإجمالي
        $discount = min($discount, $gross);

        $newTotal = round($gross - $discount, 2);

        return [
            'ok' => true,
            'coupon' => $coupon,
            'promotion' => $promotion,
            'eligible_base' => round($eligibleBase, 2),
            'gross_total' => round($gross, 2),
            'discount' => round($discount, 2),
            'total_after_discount' => $newTotal,
        ];
    }

    public function apply(Invoice $invoice, User $user, string $code, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($invoice, $user, $code, $actorId) {

            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->first();

            if ($invoice->status !== 'unpaid' || $invoice->is_locked) {
                return [
                    'ok' => false,
                    'message' => 'Cannot apply coupon on this invoice.',
                    'errors' => ['invoice' => ['Cannot apply coupon on this invoice.']],
                ];
            }

            $coupon = PromotionCoupon::query()
                ->where('code', strtoupper(trim($code)))
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                return ['ok' => false, 'message' => 'Invalid coupon code.', 'errors' => ['code' => ['Invalid coupon code.']]];
            }

            // تأكد صلاحية الكوبون/الحملة
            $valid = $this->preview($invoice, $user, $coupon->code);
            if (!$valid['ok'])
                return $valid;

            // تحقق limits (باستخدام جدول redemptions)
            $totalUsed = PromotionCouponRedemption::query()
                ->where('promotion_coupon_id', $coupon->id)
                ->where('status', 'applied')
                ->count();

            if ($coupon->usage_limit_total !== null && $totalUsed >= (int) $coupon->usage_limit_total) {
                return ['ok' => false, 'message' => 'Coupon usage limit reached.', 'errors' => ['code' => ['Coupon usage limit reached.']]];
            }

            $userUsed = PromotionCouponRedemption::query()
                ->where('promotion_coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->where('status', 'applied')
                ->count();

            if ($coupon->usage_limit_per_user !== null && $userUsed >= (int) $coupon->usage_limit_per_user) {
                return ['ok' => false, 'message' => 'You already used this coupon.', 'errors' => ['code' => ['You already used this coupon.']]];
            }

            // لو كان على الفاتورة كوبون سابق → void
            $meta = is_array($invoice->meta) ? $invoice->meta : (json_decode($invoice->meta ?? '[]', true) ?: []);
            $oldCouponId = $meta['coupon']['promotion_coupon_id'] ?? null;

            if ($oldCouponId) {
                PromotionCouponRedemption::query()
                    ->where('invoice_id', $invoice->id)
                    ->where('status', 'applied')
                    ->update([
                        'status' => 'voided',
                        'voided_at' => now(),
                        'updated_by' => $actorId,
                        'updated_at' => now(),
                    ]);
            }

            $discount = (float) $valid['discount'];

            // تحديث invoice totals
            $gross = (float) $invoice->subtotal + (float) $invoice->tax;
            $newTotal = round(max(0, $gross - $discount), 2);

            $meta['coupon'] = [
                'code' => $coupon->code,
                'promotion_coupon_id' => $coupon->id,
                'promotion_id' => $coupon->promotion_id,
                'eligible_base' => (float) $valid['eligible_base'],
                'discount' => $discount,
                'applied_at' => now()->toDateTimeString(),
            ];

            $invoice->update([
                'discount' => $discount,
                'total' => $newTotal,
                'meta' => $meta,
                'updated_by' => $actorId,
            ]);

            // سجل redemption
            PromotionCouponRedemption::create([
                'promotion_coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'discount_amount' => $discount,
                'status' => 'applied',
                'applied_at' => now(),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // used_count (اختياري للتسريع)
            $coupon->update([
                'used_count' => PromotionCouponRedemption::query()
                    ->where('promotion_coupon_id', $coupon->id)
                    ->where('status', 'applied')
                    ->count(),
            ]);

            return ['ok' => true, 'invoice' => $invoice->fresh(['items', 'payments'])];
        });
    }

    private function findValidCoupon(string $code): PromotionCoupon
    {
        $code = strtoupper(trim($code));

        $coupon = PromotionCoupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            abort(422, 'Invalid coupon code.');
        }

        $today = now()->toDateString();

        if ($coupon->starts_at && $coupon->starts_at->toDateString() > $today) {
            abort(422, 'Coupon not active yet.');
        }
        if ($coupon->ends_at && $coupon->ends_at->toDateString() < $today) {
            abort(422, 'Coupon expired.');
        }

        $promotion = $coupon->promotion;
        if (!$promotion || !$promotion->is_active)
            abort(422, 'Promotion not active.');

        if ($promotion->starts_at && $promotion->starts_at->toDateString() > $today)
            abort(422, 'Promotion not active yet.');
        if ($promotion->ends_at && $promotion->ends_at->toDateString() < $today)
            abort(422, 'Promotion expired.');

        return $coupon->load('promotion');
    }

    private function eligibleBase(Invoice $invoice, $promotion): float
    {
        $base = 0.0;

        foreach ($invoice->items as $item) {
            // نخصم على أساس qty*unit_price (بدون tax)
            $lineBase = ((float) $item->qty) * ((float) $item->unit_price);

            $isService = $item->itemable_type === 'App\\Models\\Service';
            $isPackage = $item->itemable_type === 'App\\Models\\Package';

            if ($promotion->applies_to === 'service' && !$isService)
                continue;
            if ($promotion->applies_to === 'package' && !$isPackage)
                continue;

            if ($isService) {
                if ($promotion->apply_all_services) {
                    $base += $lineBase;
                    continue;
                }
                $allowed = $promotion->services
                    ->where('pivot.is_active', true)
                    ->pluck('id')
                    ->contains((int) $item->itemable_id);

                if ($allowed)
                    $base += $lineBase;
            }

            if ($isPackage) {
                if ($promotion->apply_all_packages) {
                    $base += $lineBase;
                    continue;
                }
                $allowed = $promotion->packages
                    ->where('pivot.is_active', true)
                    ->pluck('id')
                    ->contains((int) $item->itemable_id);

                if ($allowed)
                    $base += $lineBase;
            }

            // لو applies_to both: يتم احتساب اللي ينطبق
        }

        return $base;
    }

    private function calcDiscount(float $eligibleBase, $promotion, PromotionCoupon $coupon): float
    {
        $discount = 0.0;

        if ($promotion->discount_type === 'percent') {
            $discount = $eligibleBase * ((float) $promotion->discount_value / 100.0);
        } else {
            $discount = (float) $promotion->discount_value;
        }

        // لا تتجاوز eligibleBase
        $discount = min($discount, $eligibleBase);

        // max_discount من coupon إن وجد وإلا من promotion
        $max = $coupon->max_discount !== null ? (float) $coupon->max_discount : ($promotion->max_discount !== null ? (float) $promotion->max_discount : null);
        if ($max !== null)
            $discount = min($discount, $max);

        return round($discount, 2);
    }

    public function removeCoupon(Invoice $invoice, User $user, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($invoice, $user, $actorId) {

            $invoice = \App\Models\Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->first();

            if ($invoice->user_id !== $user->id) {
                return [
                    'ok' => false,
                    'message' => 'Not found',
                    'errors' => ['invoice' => ['Not found']],
                ];
            }

            // فقط قبل الدفع
            if ($invoice->status !== 'unpaid' || $invoice->is_locked) {
                return [
                    'ok' => false,
                    'message' => 'Cannot remove coupon from this invoice.',
                    'errors' => ['invoice' => ['Cannot remove coupon from this invoice.']],
                ];
            }

            $meta = is_array($invoice->meta) ? $invoice->meta : (json_decode($invoice->meta ?? '[]', true) ?: []);
            $hasCoupon = isset($meta['coupon']['promotion_coupon_id']);

            if (!$hasCoupon) {
                // ما في كوبون أصلاً
                $invoice->loadMissing(['items', 'payments']);
                return ['ok' => true, 'invoice' => $invoice];
            }

            // 1) void أي redemption active لهذه الفاتورة
            PromotionCouponRedemption::query()
                ->where('invoice_id', $invoice->id)
                ->where('status', 'applied')
                ->update([
                    'status' => 'voided',
                    'voided_at' => now(),
                    'updated_by' => $actorId,
                    'updated_at' => now(),
                ]);

            // 2) رجّع totals كما كانت (discount=0)
            $gross = (float) $invoice->subtotal + (float) $invoice->tax;

            unset($meta['coupon']);

            $invoice->update([
                'discount' => 0,
                'total' => round($gross, 2),
                'meta' => $meta,
                'updated_by' => $actorId,
            ]);

            $invoice->loadMissing(['items', 'payments']);

            return ['ok' => true, 'invoice' => $invoice];
        });
    }
    
}