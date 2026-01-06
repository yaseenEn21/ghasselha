<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AvailableCouponResource;
use App\Http\Resources\Api\UsedCouponResource;
use App\Models\PromotionCoupon;
use App\Models\PromotionCouponRedemption;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * GET /api/v1/coupons?tab=available|used
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return api_error('Unauthenticated', 401);
        }

        $tab = $request->input('tab', 'available'); // available|used

        // =========================
        // Used tab
        // =========================
        if ($tab === 'used') {
            $q = PromotionCouponRedemption::query()
                ->where('user_id', $user->id)
                ->where('status', 'applied')
                ->with(['coupon.promotion'])
                ->orderByDesc('applied_at');

            $paginator = $q->paginate(50);
            $paginator->setCollection(
                $paginator->getCollection()->map(fn($row) => new UsedCouponResource($row))
            );

            return api_paginated($paginator, 'Used coupons');
        }

        // =========================
        // Available tab
        // =========================
        $today = now()->toDateString();

        $q = PromotionCoupon::query()
            ->where('is_active', true)
            // coupon period
            ->where(function ($x) use ($today) {
                $x->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
            })
            ->where(function ($x) use ($today) {
                $x->whereNull('ends_at')->orWhere('ends_at', '>=', $today);
            })
            // promotion must be active & in period
            ->whereHas('promotion', function ($pq) use ($today) {
                $pq->where('is_active', true)
                    ->where(function ($x) use ($today) {
                        $x->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
                    })
                    ->where(function ($x) use ($today) {
                        $x->whereNull('ends_at')->orWhere('ends_at', '>=', $today);
                    });
            })
            ->with('promotion')
            // user used count
            ->withCount([
                'redemptions as user_used_count' => function ($rq) use ($user) {
                    $rq->where('user_id', $user->id)->where('status', 'applied');
                }
            ]);

        // بحث بالكود
        if ($request->filled('q')) {
            $search = strtoupper(trim((string) $request->input('q')));
            $q->where('code', 'like', "%{$search}%");
        }

        // ✅ استبعاد اللي وصل حدّه لكل مستخدم (بدون HAVING)
        $q->where(function ($x) use ($user) {
            $x->whereNull('usage_limit_per_user')
                ->orWhereRaw('(
                select count(*)
                from promotion_coupon_redemptions r
                where r.promotion_coupon_id = promotion_coupons.id
                  and r.user_id = ?
                  and r.status = "applied"
            ) < usage_limit_per_user', [$user->id]);
        });

        // ✅ استبعاد اللي وصل حدّه الإجمالي (باستخدام used_count للتسريع)
        $q->where(function ($x) {
            $x->whereNull('usage_limit_total')
                ->orWhereRaw('used_count < usage_limit_total');
        });

        $paginator = $q->orderByDesc('id')->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn($row) => new AvailableCouponResource($row))
        );

        return api_paginated($paginator, 'Available coupons');
    }

}