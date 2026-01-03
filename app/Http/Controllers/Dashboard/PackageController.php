<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PackageController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:packages.view')->only(['index', 'show']);
        $this->middleware('can:packages.create')->only(['create', 'store']);
        $this->middleware('can:packages.edit')->only(['edit', 'update']);
        $this->middleware('can:packages.delete')->only(['destroy']);

        $this->title = t('packages.list');
        $this->page_title = t('packages.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = Package::query()
                ->select('packages.*')
                ->orderBy('sort_order')
                ->orderBy('id');

            // 🔍 البحث بالاسم (JSON name->ar / name->en)
            if ($search = $request->get('search_custom')) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            }

            // 🎛 فلتر الحالة
            if ($status = $request->get('status')) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            return $datatable->eloquent($query)
                ->editColumn('name', function (Package $row) {
                    $locale = app()->getLocale();
                    $name = $row->name[$locale] ?? reset($row->name ?? []) ?? '';
                    return e($name);
                })
                ->editColumn('price', fn(Package $row) => number_format($row->price, 2))
                ->editColumn('discounted_price', function (Package $row) {
                    return $row->discounted_price !== null
                        ? number_format($row->discounted_price, 2)
                        : '—';
                })
                ->editColumn('validity_days', function (Package $row) {
                    return $row->validity_days;
                })
                ->editColumn('washes_count', fn(Package $row) => $row->washes_count)
                ->addColumn('is_active_badge', function (Package $row) {
                    if ($row->is_active) {
                        $label = __('packages.active');
                        return '<span class="badge badge-light-success">' . $label . '</span>';
                    }

                    $label = __('packages.inactive');
                    return '<span class="badge badge-light-danger">' . $label . '</span>';
                })
                ->editColumn('created_at', fn(Package $row) => optional($row->created_at)->format('Y-m-d'))
                ->addColumn('actions', function (Package $row) {
                    $editUrl = route('dashboard.packages.edit', $row->id);
                    $canEdit = auth()->user()->can('packages.edit') ?? true;
                    $canDelete = auth()->user()->can('packages.delete') ?? true;

                    $html = '<div class="d-flex gap-2">';

                    if ($canEdit) {
                        $html .= '<a href="' . $editUrl . '" class="btn btn-sm btn-light-warning">'
                            . e(__('packages.edit')) . '</a>';
                    }

                    if ($canDelete) {
                        $html .= '
                            <button type="button"
                                class="btn btn-sm btn-light-danger js-delete-package"
                                data-id="' . $row->id . '">
                                ' . e(__('packages.delete')) . '
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['is_active_badge', 'actions'])
                ->make(true);
        }

        return view('dashboard.packages.index');
    }

    public function create()
    {
        $services = Service::orderBy('sort_order')->orderBy('id')->get();

        return view('dashboard.packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'washes_count' => ['required', 'integer', 'min:1'],
            'position' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5048'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ]);

        $package = DB::transaction(function () use ($request, $data) {

            // 🚦 حساب sort_order كما عملنا في الخدمات
            $desiredPosition = $data['position'] ?? null;

            if ($desiredPosition) {
                Package::where('sort_order', '>=', $desiredPosition)
                    ->increment('sort_order');
            } else {
                $max = Package::max('sort_order') ?? 0;
                $desiredPosition = $max + 1;
            }

            $payload = [
                'name' => $request->input('name', []),
                'description' => $request->input('description', []),
                'price' => $data['price'],
                'discounted_price' => $data['discounted_price'] ?? null,
                'validity_days' => $data['validity_days'],
                'washes_count' => $data['washes_count'],
                'sort_order' => $desiredPosition,
                'is_active' => $request->boolean('is_active', true),
            ];

            $package = Package::create($payload);

            // 🖼️ صورة
            if ($request->hasFile('image_ar')) {
                $package->addMediaFromRequest('image_ar')->toMediaCollection('image');
            }

            if ($request->hasFile('image_en')) {
                $package->addMediaFromRequest('image_en')->toMediaCollection('image');
            }

            // 🔗 الخدمات داخل الباقة مع sort_order في pivot
            $serviceIds = $request->input('services', []);
            $syncData = [];
            foreach (array_values($serviceIds) as $index => $serviceId) {
                $syncData[$serviceId] = ['sort_order' => $index + 1];
            }
            if (!empty($syncData)) {
                $package->services()->sync($syncData);
            }

            return $package;
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => __('packages.created_successfully'),
                'redirect' => route('dashboard.packages.index'),
                'data' => ['id' => $package->id],
            ]);
        }

        return redirect()
            ->route('dashboard.packages.index')
            ->with('success', __('packages.created_successfully'));
    }

    // public function show(Package $package)
    // {
    //     $package->load([
    //         'services.category',
    //         'subscriptions',
    //     ]);

    //     return view('dashboard.packages.show', compact('package'));
    // }

    public function edit(Package $package)
    {
        $services = Service::orderBy('sort_order')->orderBy('id')->get();
        $selectedServices = $package->services()->pluck('services.id')->toArray();

        return view('dashboard.packages.edit', compact('package', 'services', 'selectedServices'));
    }

    public function update(Request $request, Package $package)
    {
        $data = $request->validate([
            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'washes_count' => ['required', 'integer', 'min:1'],
            'position' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ]);

        DB::transaction(function () use ($request, $data, $package) {

            $oldPosition = $package->sort_order ?? (Package::max('sort_order') + 1);
            $newPosition = $data['position'] ?? $oldPosition;

            if ($newPosition != $oldPosition) {
                if ($newPosition < $oldPosition) {
                    Package::where('id', '!=', $package->id)
                        ->whereBetween('sort_order', [$newPosition, $oldPosition - 1])
                        ->increment('sort_order');
                } else {
                    Package::where('id', '!=', $package->id)
                        ->whereBetween('sort_order', [$oldPosition + 1, $newPosition])
                        ->decrement('sort_order');
                }
            }

            $payload = [
                'name' => $request->input('name', []),
                'description' => $request->input('description', []),
                'price' => $data['price'],
                'discounted_price' => $data['discounted_price'] ?? null,
                'validity_days' => $data['validity_days'],
                'washes_count' => $data['washes_count'],
                'sort_order' => $newPosition,
                'is_active' => $request->boolean('is_active', true),
            ];

            $package->update($payload);

            if ($request->hasFile('image_ar')) {
                $package->addMediaFromRequest('image_ar')->toMediaCollection('image');
            }

            if ($request->hasFile('image_en')) {
                $package->addMediaFromRequest('image_en')->toMediaCollection('image');
            }

            // تحديث الخدمات في البايفوت
            $serviceIds = $request->input('services', []);
            $syncData = [];
            foreach (array_values($serviceIds) as $index => $serviceId) {
                $syncData[$serviceId] = ['sort_order' => $index + 1];
            }
            $package->services()->sync($syncData);
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => __('packages.updated_successfully'),
                'redirect' => route('dashboard.packages.index'),
            ]);
        }

        return redirect()
            ->route('dashboard.packages.index')
            ->with('success', __('packages.updated_successfully'));
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return response()->json([
            'message' => __('packages.deleted_successfully'),
        ]);
    }
}