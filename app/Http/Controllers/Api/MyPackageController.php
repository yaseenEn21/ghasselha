<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PackageSubscriptionResource;
use App\Http\Resources\Api\PackageResource;
use App\Models\PackageSubscription;
use App\Models\Package;
use Illuminate\Http\Request;

class MyPackageController extends Controller
{
    public function __construct()
    {
        //
    }

    /**
     * GET /api/v1/my-packages
     * optional: ?status=active|expired|cancelled
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->input('status');

        $q = PackageSubscription::query()
            ->where('user_id', $user->id)
            ->with(['package' => function ($pq) {
                $pq->where('is_active', true);
            }])
            ->orderBy('ends_at', 'desc');

        if (in_array($status, ['active', 'expired', 'cancelled'])) {
            $q->where('status', $status);
        }

        // الافتراضي: “باقاتي الحالية” = active فعّالة
        if (!$status) {
            $q->active();
        }

        $paginator = $q->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($item) => new PackageSubscriptionResource($item))
        );

        return api_paginated($paginator);
    }

    // public function index(Request $request)
    // {
    //     $user = $request->user(); // هنا مضمون لأنه داخل auth:sanctum
    //     $withServices = $request->boolean('with_services');
    //     $status = $request->input('status') ?? 'active';

    //     $activeSubFilter = function ($sq) use ($user, $status) {
    //         $sq->where('user_id', $user->id)
    //             ->where('status', $status)
    //             ->whereDate('ends_at', '>=', now()->toDateString())
    //             ->where('remaining_washes', '>', 0) // ✅ اعتبرها غير فعالة لو الرصيد صفر
    //             ->orderBy('ends_at', 'desc')
    //             ->limit(1);
    //     };

    //     $q = Package::query()
    //         ->where('is_active', true)
    //         // ✅ فقط الباقات اللي عنده اشتراك فعّال فيها
    //         ->whereHas('subscriptions', function ($sq) use ($user) {
    //             $sq->where('user_id', $user->id)
    //                 ->where('status', 'active')
    //                 ->whereDate('ends_at', '>=', now()->toDateString())
    //                 ->where('remaining_washes', '>', 0);
    //         })
    //         // ✅ تحميل الاشتراك (عشان PackageResource يطلع is_subscribed_active + remaining_washes ...)
    //         ->with(['subscriptions' => $activeSubFilter]);

    //     if ($withServices) {
    //         $q->with(['services:id,name,duration_minutes']);
    //     }

    //     // ترتيب حسب أقرب/أبعد انتهاء (اختار اللي بدك)
    //     $q->orderByDesc(
    //         PackageSubscription::select('ends_at')
    //             ->whereColumn('package_id', 'packages.id')
    //             ->where('user_id', $user->id)
    //             ->where('status', 'active')
    //             ->whereDate('ends_at', '>=', now()->toDateString())
    //             ->where('remaining_washes', '>', 0)
    //             ->limit(1)
    //     );

    //     $paginator = $q->paginate(50);

    //     $paginator->setCollection(
    //         $paginator->getCollection()->map(fn ($item) => new PackageResource($item))
    //     );

    //     return api_paginated($paginator);
    // }

}