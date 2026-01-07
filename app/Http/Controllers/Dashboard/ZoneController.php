<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class ZoneController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        // عدّل الصلاحيات حسب نظامك
        $this->middleware('can:zones.view')->only(['index', 'datatable']);
        $this->middleware('can:zones.create')->only(['create', 'store']);
        $this->middleware('can:zones.edit')->only(['edit', 'update']);
        $this->middleware('can:zones.delete')->only(['destroy']);

        $this->title = __('zones.title');
        $this->page_title = __('zones.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        return view('dashboard.zones.index');
    }

    public function datatable(DataTables $datatable, Request $request)
    {
        $query = Zone::query()
            ->select('zones.*')
            ->withCount('servicePrices')
            ->latest('id');

        if ($search = trim((string) $request->get('search_custom'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            if ($status === 'active')
                $query->where('is_active', true);
            if ($status === 'inactive')
                $query->where('is_active', false);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        return $datatable->eloquent($query)
            ->addColumn('polygon_badge', function (Zone $row) {
                $has = is_array($row->polygon) && !empty($row->polygon);
                return $has
                    ? '<span class="badge badge-light-success">' . e(__('zones.has_polygon')) . '</span>'
                    : '<span class="badge badge-light-warning">' . e(__('zones.no_polygon')) . '</span>';
            })
            ->addColumn('bbox_label', function (Zone $row) {
                if ($row->min_lat === null || $row->min_lng === null || $row->max_lat === null || $row->max_lng === null) {
                    return '—';
                }
                return e($row->min_lat . ', ' . $row->min_lng . ' → ' . $row->max_lat . ', ' . $row->max_lng);
            })
            ->addColumn('center_label', function (Zone $row) {
                if ($row->center_lat === null || $row->center_lng === null)
                    return '—';
                return e($row->center_lat . ', ' . $row->center_lng);
            })
            ->addColumn('prices_count', fn(Zone $row) => (int) ($row->service_prices_count ?? 0))
            ->addColumn('is_active_badge', function (Zone $row) {
                return $row->is_active
                    ? '<span class="badge badge-light-success">' . e(__('zones.active')) . '</span>'
                    : '<span class="badge badge-light-danger">' . e(__('zones.inactive')) . '</span>';
            })
            ->addColumn('created_at_label', fn(Zone $row) => $row->created_at?->format('Y-m-d') ?? '—')
            ->addColumn('actions', fn(Zone $row) => view('dashboard.zones._actions', compact('row'))->render())
            ->rawColumns(['polygon_badge', 'is_active_badge', 'actions'])
            ->make(true);
    }

    public function show(Zone $zone)
    {
        $this->title = __('zones.view');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        $zone->loadMissing([
            'servicePrices' => function ($q) {
                $q->with(['service:id,name,price,discounted_price'])
                    ->latest('id');
            },
        ]);

        return view('dashboard.zones.show', compact('zone'));
    }

    public function create()
    {
        view()->share([
            'title' => __('zones.create'),
            'page_title' => __('zones.create'),
        ]);

        return view('dashboard.zones.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateZone($request);

        $payload = $this->buildPayload($request, $data);
        $zone = Zone::create($payload);

        return $request->ajax()
            ? response()->json([
                'message' => __('zones.created_successfully'),
                'redirect' => route('dashboard.zones.index'),
                'data' => ['id' => $zone->id],
            ])
            : redirect()->route('dashboard.zones.index')->with('success', __('zones.created_successfully'));
    }

    public function edit(Zone $zone)
    {
        view()->share([
            'title' => __('zones.edit'),
            'page_title' => __('zones.edit'),
        ]);

        return view('dashboard.zones.edit', compact('zone'));
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $this->validateZone($request, $zone->id);

        $zone->update($this->buildPayload($request, $data, $zone));

        return $request->ajax()
            ? response()->json([
                'message' => __('zones.updated_successfully'),
                'redirect' => route('dashboard.zones.index'),
                'data' => ['id' => $zone->id],
            ])
            : redirect()->route('dashboard.zones.index')->with('success', __('zones.updated_successfully'));
    }

    public function destroy(Request $request, Zone $zone)
    {
        $zone->update(['updated_by' => $request->user()?->id]);
        $zone->delete();

        return $request->ajax()
            ? response()->json(['message' => __('zones.deleted_successfully')])
            : redirect()->route('dashboard.zones.index')->with('success', __('zones.deleted_successfully'));
    }

    private function validateZone(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:190',
                Rule::unique('zones', 'name')->ignore($ignoreId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            // polygon ممكن يجي JSON string
            'polygon' => ['nullable'],
        ]);
    }

    private function buildPayload(Request $request, array $data, ?Zone $zone = null): array
    {
        $polygon = $this->normalizePolygon($request->input('polygon'));
        [$bbox, $center] = $this->computeBboxAndCenter($polygon);

        return [
            'name' => trim((string) $data['name']),
            'polygon' => $polygon,

            'min_lat' => $bbox['min_lat'],
            'max_lat' => $bbox['max_lat'],
            'min_lng' => $bbox['min_lng'],
            'max_lng' => $bbox['max_lng'],

            'center_lat' => $center['lat'],
            'center_lng' => $center['lng'],

            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),

            'created_by' => $zone ? $zone->created_by : $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ];
    }

    /**
     * يقبل:
     * - null
     * - array نقاط: [ {"lat":..,"lng":..}, ... ]
     * - GeoJSON-ish: {"type":"Polygon","coordinates":[[[lng,lat],[lng,lat],...]]}
     * - json string لأي من السابق
     */
    private function normalizePolygon($input): ?array
    {
        if ($input === null || $input === '')
            return null;

        if (is_string($input)) {
            $decoded = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE)
                return null;
            $input = $decoded;
        }

        if (!is_array($input))
            return null;

        // GeoJSON Polygon
        if (($input['type'] ?? null) === 'Polygon' && isset($input['coordinates'][0]) && is_array($input['coordinates'][0])) {
            $ring = $input['coordinates'][0];
            $points = [];
            foreach ($ring as $pair) {
                if (!is_array($pair) || count($pair) < 2)
                    continue;
                $lng = (float) $pair[0];
                $lat = (float) $pair[1];
                $points[] = ['lat' => $lat, 'lng' => $lng];
            }
            return count($points) >= 3 ? $points : null;
        }

        // Array of points [{lat,lng},...]
        $points = [];
        foreach ($input as $p) {
            if (!is_array($p))
                continue;
            if (!array_key_exists('lat', $p) || !array_key_exists('lng', $p))
                continue;
            $points[] = ['lat' => (float) $p['lat'], 'lng' => (float) $p['lng']];
        }

        return count($points) >= 3 ? $points : null;
    }

    private function computeBboxAndCenter(?array $polygon): array
    {
        if (!$polygon || count($polygon) < 3) {
            return [
                ['min_lat' => null, 'max_lat' => null, 'min_lng' => null, 'max_lng' => null],
                ['lat' => null, 'lng' => null],
            ];
        }

        $lats = array_map(fn($p) => (float) $p['lat'], $polygon);
        $lngs = array_map(fn($p) => (float) $p['lng'], $polygon);

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        $centerLat = ($minLat + $maxLat) / 2;
        $centerLng = ($minLng + $maxLng) / 2;

        return [
            ['min_lat' => $minLat, 'max_lat' => $maxLat, 'min_lng' => $minLng, 'max_lng' => $maxLng],
            ['lat' => $centerLat, 'lng' => $centerLng],
        ];
    }
}