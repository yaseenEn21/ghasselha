<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ServiceResource;
use App\Services\ZoneResolverService;
use Carbon\Carbon;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user('sanctum');
        $groupId = $user?->customer_group_id;

        // zone_id إما مباشر أو محسوب من lat/lng
        $zoneId = $request->integer('zone_id') ?: null;

        if (!$zoneId && $request->filled(['lat', 'lng'])) {
            $zoneId = app(ZoneResolverService::class)
                ->resolveId((float) $request->input('lat'), (float) $request->input('lng'));
        }

        $timePeriod = $this->resolveTimePeriod($request); // morning|evening

        // نخزنهم على request عشان الـResource يستخدمهم بدون إعادة حساب
        $request->attributes->set('zone_id', $zoneId);
        $request->attributes->set('time_period', $timePeriod);

        $q = Service::query()
            ->where('is_active', true)
            ->whereHas('category', fn($cq) => $cq->where('is_active', true))
            ->with(['category:id,name,sort_order,is_active'])
            ->orderBy('id', 'desc');

        // Group pricing (أولوية)
        if ($groupId) {
            $q->with([
                'groupPrices' => function ($pq) use ($groupId) {
                    $pq->where('customer_group_id', $groupId)->where('is_active', true);
                }
            ]);
        }

        // Zone pricing (فقط إذا قدرنا نحدد zone)
        if ($zoneId) {
            $tp = in_array($timePeriod, ['morning', 'evening']) ? $timePeriod : 'all';

            $q->with([
                'zonePrices' => function ($zp) use ($zoneId, $tp) {
                    $zp->where('zone_id', $zoneId)
                        ->where('is_active', true)
                        ->whereIn('time_period', [$tp, 'all'])
                        ->orderByRaw("CASE WHEN time_period = ? THEN 0 ELSE 1 END", [$tp]);
                }
            ]);
        }

        if ($request->filled('service_category_id')) {
            $q->where('service_category_id', $request->integer('service_category_id'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $lang = request_lang();

            $q->where(function ($qq) use ($search, $lang) {
                $qq->where("name->$lang", 'like', "%{$search}%")
                    ->orWhere("name->en", 'like', "%{$search}%");
            });
        }

        $paginator = $q->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn($item) => new ServiceResource($item))
        );

        return api_paginated($paginator);
    }

    private function resolveTimePeriod(Request $request): string
    {
        // لو أرسلت time_period مباشرة
        $tp = $request->input('time_period');
        if (in_array($tp, ['morning', 'evening']))
            return $tp;

        // أو نستنتجها من scheduled_at (أو الآن)
        try {
            $dt = $request->filled('scheduled_at') ? Carbon::parse($request->input('scheduled_at')) : now();
        } catch (\Throwable $e) {
            $dt = now();
        }

        $hour = (int) $dt->format('H');
        return ($hour >= 6 && $hour < 16) ? 'morning' : 'evening';
    }

    public function show(Request $request, Service $service)
    {
        if (!$service->is_active)
            return api_error('Not found', 404);
        if (!$service->category()->where('is_active', true)->exists())
            return api_error('Not found', 404);

        $user = $request->user('sanctum');
        $groupId = $user?->customer_group_id;

        $zoneId = $request->integer('zone_id') ?: null;
        if (!$zoneId && $request->filled(['lat', 'lng'])) {
            $zoneId = app(ZoneResolverService::class)
                ->resolveId((float) $request->input('lat'), (float) $request->input('lng'));
        }

        $timePeriod = $this->resolveTimePeriod($request);
        $request->attributes->set('zone_id', $zoneId);
        $request->attributes->set('time_period', $timePeriod);

        $service->load('category:id,name,sort_order,is_active');

        if ($groupId) {
            $service->load(['groupPrices' => fn($pq) => $pq->where('customer_group_id', $groupId)->where('is_active', true)]);
        }

        if ($zoneId) {
            $tp = in_array($timePeriod, ['morning', 'evening']) ? $timePeriod : 'all';
            $service->load([
                'zonePrices' => fn($zp) => $zp->where('zone_id', $zoneId)->where('is_active', true)
                    ->whereIn('time_period', [$tp, 'all'])
                    ->orderByRaw("CASE WHEN time_period = ? THEN 0 ELSE 1 END", [$tp])
            ]);
        }

        return api_success(new ServiceResource($service));
    }


}
