<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PackagePurchaseRequest;
use App\Http\Resources\Api\PackageSubscriptionResource;
use App\Models\Package;
use App\Models\PackageSubscription;
use Illuminate\Support\Facades\DB;

class PackagePurchaseController extends Controller
{
    /**
     * POST /api/v1/packages/{package}/purchase
     */
    public function store(PackagePurchaseRequest $request, Package $package, \App\Services\PackagePurchaseService $svc)
    {
        if (!$package->is_active) {
            return api_error('Package not found', 404);
        }

        $user = $request->user();

        // منع شراء نفس الباقة لو عنده اشتراك فعّال
        $alreadyActive = PackageSubscription::query()
            ->where('user_id', $user->id)
            ->where('package_id', $package->id)
            ->active()
            ->exists();

        if ($alreadyActive) {
            return api_error('You already have an active subscription for this package.', 409);
        }

        $invoice = $svc->createInvoiceForPackage($user->id, $package, $user->id);

        // رجّع الفاتورة (التطبيق بعدها يدفعها عبر /invoices/{id}/payments)
        return api_success(new \App\Http\Resources\Api\InvoiceResource($invoice), 'Invoice created', 201);
    }

    // public function store(PackagePurchaseRequest $request, Package $package)
    // {
    //     if (!$package->is_active) {
    //         return api_error('Package not found', 404);
    //     }

    //     $user = $request->user();

    //     $subscription = DB::transaction(function () use ($user, $package) {

    //         $startsAt = now()->toDateString();
    //         $endsAt = now()->addDays((int) $package->validity_days)->toDateString();

    //         $price = (float) $package->price;
    //         $disc = $package->discounted_price !== null ? (float) $package->discounted_price : null;
    //         $final = $disc ?? $price;

    //         $totalWashes = (int) $package->washes_count;

    //         return PackageSubscription::create([
    //             'user_id' => $user->id,
    //             'package_id' => $package->id,

    //             'starts_at' => $startsAt,
    //             'ends_at' => $endsAt,
    //             'status' => 'active',

    //             'price_snapshot' => $price,
    //             'discounted_price_snapshot' => $disc,
    //             'final_price_snapshot' => $final,

    //             'total_washes_snapshot' => $totalWashes,
    //             'remaining_washes' => $totalWashes,

    //             'purchased_at' => now(),
    //         ]);
    //     });

    //     // ✅ هان بنرجّع نفس شكل my-packages: PackageResource + الاشتراك الفعّال لهذا المستخدم
    //     $activeSubFilter = function ($sq) use ($user) {
    //         $sq->where('user_id', $user->id)
    //             ->where('status', 'active')
    //             ->whereDate('ends_at', '>=', now()->toDateString())
    //             ->where('remaining_washes', '>', 0)
    //             ->orderBy('ends_at', 'desc')
    //             ->limit(1);
    //     };

    //     $package = $subscription->package()->first(); // جلب الباقة
    //     $package->load([
    //         'subscriptions' => $activeSubFilter,
    //     ]);

    //     // (اختياري) إذا بدك تفاصيل خدمات الباقة في نفس الريسبونس:
    //     if ($request->boolean('with_services')) {
    //         $package->load(['services:id,name,duration_minutes']);
    //     }

    //     return api_success(new PackageResource($package), 'Package purchased successfully', 201);
    // }
}