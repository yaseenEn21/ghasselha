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
        $coupon = $this->findValidCoupon($code); // loads promotion too
        $invoice->loadMissing('items');

        $eligibleBase = $this->eligibleBase($invoice, $coupon);

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

        $discount = $this->calcDiscount($eligibleBase, $coupon);

        // لا نخلي الخصم يتجاوز الإجمالي
        $discount = min($discount, $gross);

        $newTotal = round($gross - $discount, 2);

        return [
            'ok' => true,
            'coupon' => $coupon,
            'promotion' => $coupon->promotion,
            'eligible_base' => round($eligibleBase, 2),
            'gross_total' => round($gross, 2),
            'discount' => round($discount, 2),
            'total_after_discount' => $newTotal,
        ];
    }

    public function apply(Invoice $invoice, User $user, string $code, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($invoice, $user, $code, $actorId) {

            /** @var Invoice $invoice */
            $invoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status !== 'unpaid' || $invoice->is_locked) {
                return [
                    'ok' => false,
                    'message' => 'Cannot apply coupon on this invoice.',
                    'errors' => ['invoice' => ['Cannot apply coupon on this invoice.']],
                ];
            }

            /** @var PromotionCoupon|null $coupon */
            $coupon = PromotionCoupon::query()
                ->where('code', strtoupper(trim($code)))
                ->lockForUpdate()
                ->with('promotion')
                ->first();

            if (!$coupon) {
                return [
                    'ok' => false,
                    'message' => 'Invalid coupon code.',
                    'errors' => ['code' => ['Invalid coupon code.']],
                ];
            }

            // تحقق صلاحية الكوبون/الحملة + eligible + min_invoice_total
            $valid = $this->previewWithLoadedCoupon($invoice, $user, $coupon);
            if (!$valid['ok']) {
                return $valid;
            }

            // تحقق limits (بشكل آمن داخل lockForUpdate على الكوبون)
            $totalUsed = PromotionCouponRedemption::query()
                ->where('promotion_coupon_id', $coupon->id)
                ->where('status', 'applied')
                ->count();

            if ($coupon->usage_limit_total !== null && $totalUsed >= (int) $coupon->usage_limit_total) {
                return [
                    'ok' => false,
                    'message' => 'Coupon usage limit reached.',
                    'errors' => ['code' => ['Coupon usage limit reached.']],
                ];
            }

            $userUsed = PromotionCouponRedemption::query()
                ->where('promotion_coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->where('status', 'applied')
                ->count();

            if ($coupon->usage_limit_per_user !== null && $userUsed >= (int) $coupon->usage_limit_per_user) {
                return [
                    'ok' => false,
                    'message' => 'You already used this coupon.',
                    'errors' => ['code' => ['You already used this coupon.']],
                ];
            }

            // ===== لو كان على الفاتورة كوبون سابق → void + تحديث used_count للكوبون القديم =====
            $meta = $this->asArray($invoice->meta);
            $oldCouponId = (int)($meta['coupon']['promotion_coupon_id'] ?? 0);

            if ($oldCouponId > 0) {
                // void أي redemption applied لهذه الفاتورة
                PromotionCouponRedemption::query()
                    ->where('invoice_id', $invoice->id)
                    ->where('status', 'applied')
                    ->update([
                        'status' => 'voided',
                        'voided_at' => now(),
                        'updated_by' => $actorId,
                        'updated_at' => now(),
                    ]);

                // حدّث used_count للكوبون القديم (لو مختلف عن الجديد)
                if ($oldCouponId !== (int)$coupon->id) {
                    $oldCoupon = PromotionCoupon::query()
                        ->whereKey($oldCouponId)
                        ->lockForUpdate()
                        ->first();

                    if ($oldCoupon) {
                        $oldCoupon->update([
                            'used_count' => PromotionCouponRedemption::query()
                                ->where('promotion_coupon_id', $oldCoupon->id)
                                ->where('status', 'applied')
                                ->count(),
                        ]);
                    }
                }
            }

            $discount = (float) $valid['discount'];

            // تحديث invoice totals
            $gross = (float) $invoice->subtotal + (float) $invoice->tax;
            $newTotal = round(max(0, $gross - $discount), 2);

            // خزّن snapshot مفيد في meta (للعرض/التحقق)
            $meta['coupon'] = [
                'code' => $coupon->code,
                'promotion_coupon_id' => (int) $coupon->id,
                'promotion_id' => (int) $coupon->promotion_id,
                'eligible_base' => (float) $valid['eligible_base'],
                'discount' => (float) $discount,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (string) $coupon->discount_value,
                'applies_to' => $coupon->applies_to,
                'apply_all_services' => (bool) $coupon->apply_all_services,
                'apply_all_packages' => (bool) $coupon->apply_all_packages,
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

            // used_count (تحديث بعد apply)
            $coupon->update([
                'used_count' => PromotionCouponRedemption::query()
                    ->where('promotion_coupon_id', $coupon->id)
                    ->where('status', 'applied')
                    ->count(),
            ]);

            return ['ok' => true, 'invoice' => $invoice->fresh(['items', 'payments'])];
        });
    }

    public function removeCoupon(Invoice $invoice, User $user, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($invoice, $user, $actorId) {

            $invoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int)$invoice->user_id !== (int)$user->id) {
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

            $meta = $this->asArray($invoice->meta);
            $couponId = (int)($meta['coupon']['promotion_coupon_id'] ?? 0);

            if ($couponId <= 0) {
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

            // 3) حدّث used_count للكوبون اللي انشال
            $coupon = PromotionCoupon::query()
                ->whereKey($couponId)
                ->lockForUpdate()
                ->first();

            if ($coupon) {
                $coupon->update([
                    'used_count' => PromotionCouponRedemption::query()
                        ->where('promotion_coupon_id', $coupon->id)
                        ->where('status', 'applied')
                        ->count(),
                ]);
            }

            $invoice->loadMissing(['items', 'payments']);

            return ['ok' => true, 'invoice' => $invoice];
        });
    }

    // ======================
    // Helpers
    // ======================

    private function findValidCoupon(string $code): PromotionCoupon
    {
        $code = strtoupper(trim($code));

        $coupon = PromotionCoupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->with('promotion')
            ->first();

        if (!$coupon) {
            abort(422, 'Invalid coupon code.');
        }

        $today = now()->toDateString();

        // coupon period
        if ($coupon->starts_at && $coupon->starts_at->toDateString() > $today) {
            abort(422, 'Coupon not active yet.');
        }
        if ($coupon->ends_at && $coupon->ends_at->toDateString() < $today) {
            abort(422, 'Coupon expired.');
        }

        // promotion period + active
        $promotion = $coupon->promotion;
        if (!$promotion || !$promotion->is_active) {
            abort(422, 'Promotion not active.');
        }

        if ($promotion->starts_at && $promotion->starts_at->toDateString() > $today) {
            abort(422, 'Promotion not active yet.');
        }
        if ($promotion->ends_at && $promotion->ends_at->toDateString() < $today) {
            abort(422, 'Promotion expired.');
        }

        return $coupon;
    }

    private function previewWithLoadedCoupon(Invoice $invoice, User $user, PromotionCoupon $coupon): array
    {
        // نفس منطق preview لكن بدون query جديد
        $invoice->loadMissing('items');

        // تأكد promotion محمّل
        $coupon->loadMissing('promotion');

        // نفس checks findValidCoupon (بدون abort)
        $today = now()->toDateString();

        if (!$coupon->is_active) {
            return ['ok' => false, 'message' => 'Invalid coupon code.', 'errors' => ['code' => ['Invalid coupon code.']]];
        }
        if ($coupon->starts_at && $coupon->starts_at->toDateString() > $today) {
            return ['ok' => false, 'message' => 'Coupon not active yet.', 'errors' => ['code' => ['Coupon not active yet.']]];
        }
        if ($coupon->ends_at && $coupon->ends_at->toDateString() < $today) {
            return ['ok' => false, 'message' => 'Coupon expired.', 'errors' => ['code' => ['Coupon expired.']]];
        }

        $promotion = $coupon->promotion;
        if (!$promotion || !$promotion->is_active) {
            return ['ok' => false, 'message' => 'Promotion not active.', 'errors' => ['code' => ['Promotion not active.']]];
        }
        if ($promotion->starts_at && $promotion->starts_at->toDateString() > $today) {
            return ['ok' => false, 'message' => 'Promotion not active yet.', 'errors' => ['code' => ['Promotion not active yet.']]];
        }
        if ($promotion->ends_at && $promotion->ends_at->toDateString() < $today) {
            return ['ok' => false, 'message' => 'Promotion expired.', 'errors' => ['code' => ['Promotion expired.']]];
        }

        $eligibleBase = $this->eligibleBase($invoice, $coupon);

        if ($eligibleBase <= 0) {
            return [
                'ok' => false,
                'message' => 'Coupon not applicable for this invoice.',
                'errors' => ['code' => ['Coupon not applicable for this invoice.']],
            ];
        }

        $gross = (float) $invoice->subtotal + (float) $invoice->tax;

        if ($coupon->min_invoice_total !== null && $gross < (float) $coupon->min_invoice_total) {
            return [
                'ok' => false,
                'message' => 'Invoice total is below coupon minimum.',
                'errors' => ['code' => ['Invoice total is below coupon minimum.']],
            ];
        }

        $discount = $this->calcDiscount($eligibleBase, $coupon);
        $discount = min($discount, $gross);

        return [
            'ok' => true,
            'eligible_base' => round($eligibleBase, 2),
            'gross_total' => round($gross, 2),
            'discount' => round($discount, 2),
            'total_after_discount' => round($gross - $discount, 2),
        ];
    }

    private function eligibleBase(Invoice $invoice, PromotionCoupon $coupon): float
    {
        $base = 0.0;

        $appliesTo = $coupon->applies_to; // service|package|both

        $meta = $this->asArray($coupon->meta);

        $serviceIds = array_values(array_filter(array_map('intval', (array)($meta['service_ids'] ?? []))));
        $packageIds = array_values(array_filter(array_map('intval', (array)($meta['package_ids'] ?? []))));

        foreach ($invoice->items as $item) {
            $lineBase = ((float)$item->qty) * ((float)$item->unit_price); // بدون tax

            $type = ltrim((string)$item->itemable_type, '\\');

            $isService = in_array($type, ['service', \App\Models\Service::class, 'App\\Models\\Service'], true);
            $isPackage = in_array($type, ['package', \App\Models\Package::class, 'App\\Models\\Package'], true);

            // فلترة حسب applies_to
            if ($appliesTo === 'service' && !$isService) {
                continue;
            }
            if ($appliesTo === 'package' && !$isPackage) {
                continue;
            }

            // service logic
            if ($isService && in_array($appliesTo, ['service', 'both'], true)) {
                if ($coupon->apply_all_services) {
                    $base += $lineBase;
                    continue;
                }

                if (!empty($serviceIds) && in_array((int)$item->itemable_id, $serviceIds, true)) {
                    $base += $lineBase;
                }
                continue;
            }

            // package logic
            if ($isPackage && in_array($appliesTo, ['package', 'both'], true)) {
                if ($coupon->apply_all_packages) {
                    $base += $lineBase;
                    continue;
                }

                if (!empty($packageIds) && in_array((int)$item->itemable_id, $packageIds, true)) {
                    $base += $lineBase;
                }
                continue;
            }
        }

        return $base;
    }

    private function calcDiscount(float $eligibleBase, PromotionCoupon $coupon): float
    {
        $discount = 0.0;

        if ($coupon->discount_type === 'percent') {
            $discount = $eligibleBase * ((float)$coupon->discount_value / 100.0);
        } else {
            $discount = (float)$coupon->discount_value;
        }

        // لا تتجاوز eligibleBase
        $discount = min($discount, $eligibleBase);

        // max_discount من coupon فقط
        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float)$coupon->max_discount);
        }

        return round($discount, 2);
    }

    private function asArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}