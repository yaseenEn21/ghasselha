<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * GET /api/v1/packages
     * optional:
     * - ?with_services=1 (يرجع خدمات الباقة)
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');
        $userId = $user?->id;

        $withServices = $request->boolean('with_services');

        $q = Package::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id', 'desc');

        if ($withServices) {
            $q->with(['services:id,name,duration_minutes']);
        }

        // لو مسجل دخول: حمّل اشتراكه الفعّال لكل باقة (0/1)
        if ($userId) {
            $q->with(['subscriptions' => function ($sq) use ($userId) {
                $sq->where('user_id', $userId)
                   ->where('status', 'active')
                   ->where('ends_at', '>=', now())
                   ->orderBy('ends_at', 'desc')
                   ->limit(1);
            }]);
        }

        $paginator = $q->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($item) => new PackageResource($item))
        );

        return api_paginated($paginator);
    }

    /**
     * GET /api/v1/packages/{package}
     */
    public function show(Request $request, Package $package)
    {
        if (!$package->is_active) {
            return api_error('Not found', 404);
        }

        $user = $request->user('sanctum');
        $userId = $user?->id;

        $package->load(['services:id,name,duration_minutes']);

        if ($userId) {
            $package->load(['subscriptions' => function ($sq) use ($userId) {
                $sq->where('user_id', $userId)
                   ->where('status', 'active')
                   ->where('ends_at', '>=', now())
                   ->orderBy('ends_at', 'desc')
                   ->limit(1);
            }]);
        }

        return api_success(new PackageResource($package));
    }
}