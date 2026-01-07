<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Zone;
use App\Models\ServiceZonePrice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ZoneServicePriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:zones.view')->only(['show', 'searchServices']);
        $this->middleware('can:zones.edit')->only(['store', 'update', 'destroy']);
    }

    private function assertBelongs(Zone $zone, ServiceZonePrice $servicePrice): void
    {
        if ((int)$servicePrice->zone_id !== (int)$zone->id) abort(404);
    }

    public function searchServices(Request $request, Zone $zone)
    {
        $q = trim((string)$request->get('q', ''));
        $locale = app()->getLocale();

        $timePeriod = $request->get('time_period'); // all|morning|evening
        if (!in_array($timePeriod, ['all', 'morning', 'evening'], true)) {
            $timePeriod = 'all';
        }

        $ignoreServiceId = (int) $request->get('ignore_service_id', 0); // عند edit

        $services = Service::query()
            ->select(['id', 'name'])
            ->where('is_active', true);

        if ($q !== '') {
            $services->where(function ($x) use ($q) {
                $x->where('name->ar', 'like', "%{$q}%")
                  ->orWhere('name->en', 'like', "%{$q}%");
            });
        }

        // exclude services already assigned for this zone & time_period (except ignore_service_id)
        $services->whereNotIn('id', function ($sub) use ($zone, $timePeriod, $ignoreServiceId) {
            $sub->from('service_zone_prices')
                ->select('service_id')
                ->whereNull('deleted_at')
                ->where('zone_id', $zone->id)
                ->where('time_period', $timePeriod);

            if ($ignoreServiceId > 0) {
                $sub->where('service_id', '!=', $ignoreServiceId);
            }
        });

        $items = $services->orderBy('id', 'desc')->limit(15)->get()
            ->map(function ($s) use ($locale) {
                $name = $s->name[$locale] ?? (is_array($s->name) ? (collect($s->name)->first() ?? '') : '');
                return ['id' => $s->id, 'text' => $name];
            })->values();

        return response()->json(['results' => $items]);
    }

    public function show(Zone $zone, ServiceZonePrice $servicePrice)
    {
        $this->assertBelongs($zone, $servicePrice);

        $servicePrice->loadMissing(['service:id,name,price,discounted_price']);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $servicePrice->id,
                'service_id' => $servicePrice->service_id,
                'time_period' => $servicePrice->time_period,
                'price' => (string)$servicePrice->price,
                'discounted_price' => $servicePrice->discounted_price !== null ? (string)$servicePrice->discounted_price : null,
                'is_active' => (bool)$servicePrice->is_active,
                'service_text' => i18n($servicePrice->service?->name),
            ],
        ]);
    }

    public function store(Request $request, Zone $zone)
    {
        $data = $this->validatePayload($request, $zone->id);

        $sp = ServiceZonePrice::create([
            'zone_id' => $zone->id,
            'service_id' => (int)$data['service_id'],
            'time_period' => $data['time_period'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        $sp->loadMissing(['service:id,name,price,discounted_price']);

        $rowHtml = view('dashboard.zones.partials._service_price_row', [
            'sp' => $sp,
        ])->render();

        return response()->json([
            'ok' => true,
            'message' => __('zones.service_prices.created_successfully'),
            'row_html' => $rowHtml,
            'id' => $sp->id,
        ]);
    }

    public function update(Request $request, Zone $zone, ServiceZonePrice $servicePrice)
    {
        $this->assertBelongs($zone, $servicePrice);

        $data = $this->validatePayload($request, $zone->id, $servicePrice->id);

        $servicePrice->update([
            'service_id' => (int)$data['service_id'],
            'time_period' => $data['time_period'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => $request->user()?->id,
        ]);

        $servicePrice->loadMissing(['service:id,name,price,discounted_price']);

        $rowHtml = view('dashboard.zones.partials._service_price_row', [
            'sp' => $servicePrice,
        ])->render();

        return response()->json([
            'ok' => true,
            'message' => __('zones.service_prices.updated_successfully'),
            'row_html' => $rowHtml,
            'id' => $servicePrice->id,
        ]);
    }

    public function destroy(Request $request, Zone $zone, ServiceZonePrice $servicePrice)
    {
        $this->assertBelongs($zone, $servicePrice);

        $servicePrice->delete();

        return response()->json([
            'ok' => true,
            'message' => __('zones.service_prices.deleted_successfully'),
        ]);
    }

    private function validatePayload(Request $request, int $zoneId, ?int $ignoreId = null): array
    {
        $uniqueRule = Rule::unique('service_zone_prices')
            ->where(function ($q) use ($zoneId, $request) {
                $q->whereNull('deleted_at')
                  ->where('zone_id', $zoneId)
                  ->where('time_period', $request->input('time_period'));
            });

        if ($ignoreId) $uniqueRule->ignore($ignoreId);

        return $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id', $uniqueRule],
            'time_period' => ['required', Rule::in(['all', 'morning', 'evening'])],

            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],

            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}