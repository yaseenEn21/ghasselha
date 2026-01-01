<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SlotRequest;
use App\Models\Service;
use App\Services\SlotService;

class SlotController extends Controller
{
    /**
     * GET /api/v1/services/{service}/slots?date=Y-m-d&lat=..&lng=..
     * Public (بدون تسجيل)
     */
    public function index(SlotRequest $request, SlotService $slotService)
    {
        $service = Service::query()
            ->where('id', $request->input('service_id'))
            ->where('is_active', true)
            ->first();
            
        // تأكد الخدمة فعّالة وتصنيفها فعّال
        if (
            !$service->is_active ||
            !$service->category()->where('is_active', true)->exists()
        ) {
            return api_error('Service not found', 404);
        }

        $date = $request->input('date');
        $lat  = (float) $request->input('lat');
        $lng  = (float) $request->input('lng');
        $step = $request->filled('step_minutes') ? (int) $request->input('step_minutes') : null;

        $payload = $slotService->getSlots($date, (int) $service->id, $lat, $lng, $step);

        return api_success($payload);
    }
}
