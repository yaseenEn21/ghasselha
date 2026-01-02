<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ServiceController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:services.view')->only(['index', 'show']);
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
                ->select('services.*');

            // 🔍 البحث بالاسم (JSON name->ar / name->en)
            if ($search = $request->get('search_custom')) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            }

            // 🎛 فلتر التصنيف
            if ($categoryId = $request->get('category_id')) {
                $query->where('service_category_id', $categoryId);
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
                    $editUrl = route('dashboard.services.edit', $row->id);
                    $canEdit = auth()->user()->can('services.edit');
                    $canDelete = auth()->user()->can('services.delete');

                    $html = '<div class="d-flex gap-2">';

                    if ($canEdit) {
                        $html .= '<a href="' . $editUrl . '" class="btn btn-sm btn-light-warning">'
                            . e(__('services.edit')) . '</a>';
                    }

                    if ($canDelete) {
                        $html .= '
                        <button type="button"
                            class="btn btn-sm btn-light-danger js-delete-service"
                            data-id="' . $row->id . '">
                            ' . e(__('services.delete')) . '
                        </button>
                    ';
                    }

                    $html .= '</div>';

                    return $html;
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

            // 👇 جديد
            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $payload = [
            'service_category_id' => $data['service_category_id'],
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),
            'duration_minutes' => $data['duration_minutes'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        $service = Service::create($payload);

        // 🖼️ حفظ الصور لو مرفوعة
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

    public function show(Service $service)
    {
        return null;
        // return view('dashboard.services.show', compact('service'));
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

            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $payload = [
            'service_category_id' => $data['service_category_id'],
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),
            'duration_minutes' => $data['duration_minutes'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        $service->update($payload);

        // 🖼️ لو أرسل صور جديدة، singleFile() حيستبدل القديمة تلقائيًا
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