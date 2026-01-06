<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:services.view')->only(['index', 'show', 'salesLinesDatatable', 'salesStats']);
        $this->middleware('can:services.create')->only(['create', 'store']);
        $this->middleware('can:services.edit')->only(['edit', 'update']);
        $this->middleware('can:services.delete')->only(['destroy']);

        $this->title = t('services.list');
        $this->page_title = t('services.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {

        if ($request->ajax()) {

            $query = Service::query()
                ->with('category')
                ->select('services.*')
                ->orderBy('sort_order');

            if ($search = $request->get('search_custom')) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            }

            if ($categoryId = $request->get('category_id')) {
                $query->where('service_category_id', $categoryId);
            }

            if ($status = $request->get('status')) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            return $datatable->eloquent($query)
                ->editColumn('name', function (Service $row) {
                    $locale = app()->getLocale();
                    $name = $row->name[$locale] ?? reset($row->name ?? []) ?? '';
                    return e($name);
                })
                ->addColumn('category_name', function (Service $row) {
                    if (!$row->category) {
                        return '—';
                    }

                    $catName = $row->category->name ?? null;
                    if (is_array($catName)) {
                        $locale = app()->getLocale();
                        return e($catName[$locale] ?? reset($catName) ?? '');
                    }

                    return e($catName);
                })
                ->addColumn('is_active_badge', function (Service $row) {
                    if ($row->is_active) {
                        $label = __('services.active');
                        return '<span class="badge badge-light-success">' . $label . '</span>';
                    }

                    $label = __('services.inactive');
                    return '<span class="badge badge-light-danger">' . $label . '</span>';
                })
                ->editColumn('price', fn(Service $row) => number_format($row->price, 2))
                ->editColumn('discounted_price', function (Service $row) {
                    return $row->discounted_price !== null
                        ? number_format($row->discounted_price, 2)
                        : '—';
                })
                ->editColumn('duration_minutes', function (Service $row) {
                    // X دقيقة / X min حسب اللغة
                    $suffix = __('services.minutes_suffix'); // هنضيفها تحت
                    return $row->duration_minutes . ' ' . $suffix;
                })
                ->editColumn('created_at', fn(Service $row) => optional($row->created_at)->format('Y-m-d'))
                ->addColumn('actions', function (Service $row) {
                    return view('dashboard.services._actions', ['service' => $row])->render();
                })
                ->rawColumns(['is_active_badge', 'actions'])
                ->make(true);
        }

        // نرسل التصنيفات للفلاتر
        $categories = ServiceCategory::select('id', 'name')->get();

        return view('dashboard.services.index', compact('categories'));
    }


    public function create()
    {

        $this->title = t('services.create_new');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        $categories = ServiceCategory::select('id', 'name')->get();

        return view('dashboard.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],

            // 👇 جديد
            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $desiredPosition = $data['sort_order'] ?? null;

        if ($desiredPosition) {
            Service::where('sort_order', '>=', $desiredPosition)
                ->increment('sort_order');
        } else {
            $max = Service::max('sort_order') ?? 0;
            $desiredPosition = $max + 1;
        }

        $payload = [
            'service_category_id' => $data['service_category_id'],
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),
            'duration_minutes' => $data['duration_minutes'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $desiredPosition
        ];

        $service = Service::create($payload);

        if ($request->hasFile('image_ar')) {
            $service->addMediaFromRequest('image_ar')->toMediaCollection('image_ar');
        }

        if ($request->hasFile('image_en')) {
            $service->addMediaFromRequest('image_en')->toMediaCollection('image_en');
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => 'تم إنشاء الخدمة بنجاح.',
                'redirect' => route('dashboard.services.index'),
                'data' => ['id' => $service->id],
            ]);
        }

        return redirect()
            ->route('dashboard.services.index')
            ->with('success', 'تم إنشاء الخدمة بنجاح.');
    }

    public function edit(Service $service)
    {

        $this->title = t('services.edit');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        $categories = ServiceCategory::select('id', 'name')->get();

        return view('dashboard.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],

            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $oldPosition = $service->sort_order ?? (Service::max('sort_order') + 1);
        $newPosition = $data['sort_order'] ?? $oldPosition;

        if ($newPosition != $oldPosition) {
            if ($newPosition < $oldPosition) {
                Service::where('id', '!=', $service->id)
                    ->whereNotNull('sort_order')
                    ->whereBetween('sort_order', [$newPosition, $oldPosition - 1])
                    ->increment('sort_order');
            } else {
                Service::where('id', '!=', $service->id)
                    ->whereNotNull('sort_order')
                    ->whereBetween('sort_order', [$oldPosition + 1, $newPosition])
                    ->decrement('sort_order');
            }
        }

        $payload = [
            'service_category_id' => $data['service_category_id'],
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),
            'duration_minutes' => $data['duration_minutes'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $newPosition,
        ];

        $service->update($payload);

        if ($request->hasFile('image_ar')) {
            $service->addMediaFromRequest('image_ar')->toMediaCollection('image_ar');
        }

        if ($request->hasFile('image_en')) {
            $service->addMediaFromRequest('image_en')->toMediaCollection('image_en');
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => 'تم تحديث بيانات الخدمة بنجاح.',
                'redirect' => route('dashboard.services.index'),
                'data' => ['id' => $service->id],
            ]);
        }

        return redirect()
            ->route('dashboard.services.index')
            ->with('success', 'تم تحديث بيانات الخدمة بنجاح.');
    }

    public function show(Service $service, Request $request)
    {
        [$fromDate, $toDate, $from, $to] = $this->resolveBookingDateRange($request);

        $base = Booking::query()
            ->where('service_id', $service->id)
            ->where('status', 'completed')
            ->where('service_pricing_source', '!=', 'package')
            ->whereBetween('booking_date', [$fromDate->toDateString(), $toDate->toDateString()]);

        $totalCount = (int) $base->count();
        $totalSales = (float) $base->sum('service_final_price_snapshot');

        // title
        $this->title = t('services.view');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        return view('dashboard.services.show', compact(
            'service',
            'from',
            'to',
            'totalCount',
            'totalSales'
        ));
    }

    public function salesLinesDatatable(Service $service, DataTables $datatable, Request $request)
    {
        [$fromDate, $toDate] = $this->resolveBookingDateRange($request);

        $query = Booking::query()
            ->where('service_id', $service->id)
            ->where('status', 'completed')
            ->where('service_pricing_source', '!=', 'package')
            ->whereBetween('booking_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->select([
                'id',
                'booking_date',
                'start_time',
                'end_time',
                'service_pricing_source',
                'service_final_price_snapshot',
                'created_at',
            ]);

        return $datatable->eloquent($query)
            ->addIndexColumn()
            ->addColumn('booking_id', fn($row) => '#' . $row->id)
            ->addColumn('time', fn($row) => substr($row->start_time, 0, 5) . ' - ' . substr($row->end_time, 0, 5))
            ->addColumn('pricing_source', function ($row) {
                $key = 'services.pricing_sources.' . $row->service_pricing_source;
                $label = __($key);

                $map = [
                    'base' => 'badge-light-primary',
                    'zone' => 'badge-light-info',
                    'group' => 'badge-light-warning',
                    'package' => 'badge-light-success',
                ];

                $cls = $map[$row->service_pricing_source] ?? 'badge-light';
                return '<span class="badge ' . $cls . '">' . $label . '</span>';
            })
            ->addColumn('final_price', fn($row) => number_format((float) $row->service_final_price_snapshot, 2))
            ->editColumn('booking_date', fn($row) => Carbon::parse($row->booking_date)->format('Y-m-d'))
            ->editColumn('created_at', fn($row) => optional($row->created_at)->format('Y-m-d'))
            ->rawColumns(['pricing_source'])
            ->make(true);
    }

    public function salesStats(Service $service, Request $request)
    {
        [$fromDate, $toDate] = $this->resolveBookingDateRange($request);

        $base = Booking::query()
            ->where('service_id', $service->id)
            ->where('status', 'completed')
            ->where('service_pricing_source', '!=', 'package')
            ->whereBetween('booking_date', [$fromDate->toDateString(), $toDate->toDateString()]);

        return response()->json([
            'total_count' => (int) $base->count(),
            'total_sales' => round((float) $base->sum('service_final_price_snapshot'), 2),
        ]);
    }

    private function resolveBookingDateRange(Request $request): array
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $fromDate = now()->startOfMonth()->startOfDay();
        $toDate = now()->endOfMonth()->endOfDay();

        if ($from) {
            try {
                $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            } catch (\Throwable $e) {
            }
        }
        if ($to) {
            try {
                $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            } catch (\Throwable $e) {
            }
        }

        return [$fromDate, $toDate, $fromDate->toDateString(), $toDate->toDateString()];
    }

    public function destroy(Request $request, Service $service)
    {
        $service->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'تم حذف الخدمة بنجاح.',
            ]);
        }

        return redirect()
            ->route('dashboard.services.index')
            ->with('success', 'تم حذف الخدمة بنجاح.');
    }
}