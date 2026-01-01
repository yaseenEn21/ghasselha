<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /**
     * GET /api/v1/service-categories
     * optional: ?with_services=1
     */
    public function index(Request $request)
    {
        $withServices = $request->boolean('with_services');

        $q = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->withCount(['services as services_count' => function ($sq) {
                $sq->where('is_active', true);
            }]);

        if ($withServices) {
            $q->with(['services' => function ($sq) {
                $sq->where('is_active', true)
                   ->orderBy('id', 'desc');
            }]);
        }

        $paginator = $q->paginate(50);

        // تحويل عناصر الـ paginator إلى Resources قبل api_paginated
        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($item) => new ServiceCategoryResource($item))
        );

        return api_paginated($paginator);
    }

    /**
     * GET /api/v1/service-categories/{serviceCategory}
     */
    public function show(ServiceCategory $serviceCategory)
    {
        if (!$serviceCategory->is_active) {
            return api_error('Not found', 404);
        }

        $serviceCategory->loadCount(['services as services_count' => function ($sq) {
            $sq->where('is_active', true);
        }]);

        return api_success(new ServiceCategoryResource($serviceCategory));
    }
}