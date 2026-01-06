<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Package;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PromotionController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:promotions.view')->only(['index', 'show', 'datatable', 'searchServices', 'searchPackages']);
        $this->middleware('can:promotions.create')->only(['create', 'store']);
        $this->middleware('can:promotions.edit')->only(['edit', 'update']);
        $this->middleware('can:promotions.delete')->only(['destroy']);

        $this->title = t('promotions.list');
        $this->page_title = t('promotions.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        return view('dashboard.promotions.index');
    }

    public function datatable(DataTables $datatable, Request $request)
    {
        $query = Promotion::query()
            ->select('promotions.*')
            ->withCount('coupons')
            ->latest('id');

        if ($search = trim((string)$request->get('search_custom'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name->ar', 'like', "%{$search}%")
                  ->orWhere('name->en', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'active') $query->where('is_active', true);
            if ($status === 'inactive') $query->where('is_active', false);
        }

        return $datatable->eloquent($query)
            ->addColumn('name_localized', function (Promotion $row) {
                $locale = app()->getLocale();
                $nameArr = $row->name ?? [];
                $name = is_array($nameArr) ? ($nameArr[$locale] ?? (collect($nameArr)->first() ?? '')) : '';
                return e($name);
            })
            ->addColumn('period', function (Promotion $row) {
                $s = $row->starts_at ? $row->starts_at->format('Y-m-d') : '—';
                $e = $row->ends_at ? $row->ends_at->format('Y-m-d') : '—';
                return e($s . ' → ' . $e);
            })
            ->addColumn('is_active_badge', function (Promotion $row) {
                return $row->is_active
                    ? '<span class="badge badge-light-success">' . e(__('promotions.active')) . '</span>'
                    : '<span class="badge badge-light-danger">' . e(__('promotions.inactive')) . '</span>';
            })
            ->addColumn('actions', function (Promotion $row) {
                return view('dashboard.promotions._actions', ['promotion' => $row])->render();
            })
            ->rawColumns(['is_active_badge', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->title = t('promotions.create_new');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        return view('dashboard.promotions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePromotion($request);

        $promotion = Promotion::create($this->buildPromotionPayload($request, $data));

        return $request->ajax()
            ? response()->json([
                'message' => __('promotions.created_successfully'),
                'redirect' => route('dashboard.promotions.index'),
                'data' => ['id' => $promotion->id],
            ])
            : redirect()->route('dashboard.promotions.index')->with('success', __('promotions.created_successfully'));
    }

    public function show(Promotion $promotion)
    {
        $this->title = t('promotions.view');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        return view('dashboard.promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        $this->title = t('promotions.edit');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        return view('dashboard.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validatePromotion($request, $promotion->id);

        $promotion->update($this->buildPromotionPayload($request, $data));

        return $request->ajax()
            ? response()->json([
                'message' => __('promotions.updated_successfully'),
                'redirect' => route('dashboard.promotions.index'),
                'data' => ['id' => $promotion->id],
            ])
            : redirect()->route('dashboard.promotions.index')->with('success', __('promotions.updated_successfully'));
    }

    public function destroy(Request $request, Promotion $promotion)
    {
        $promotion->delete();

        return $request->ajax()
            ? response()->json(['message' => __('promotions.deleted_successfully')])
            : redirect()->route('dashboard.promotions.index')->with('success', __('promotions.deleted_successfully'));
    }

    // ===== Select2 search (تُستخدم في صفحة الكوبون عندك) =====
    public function searchServices(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $locale = app()->getLocale();

        $query = Service::query()->select('id', 'name')->where('is_active', true);

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('name->ar', 'like', "%{$q}%")
                  ->orWhere('name->en', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('sort_order')->limit(10)->get()
            ->map(function ($s) use ($locale) {
                $nameArr = $s->name ?? [];
                $name = is_array($nameArr) ? ($nameArr[$locale] ?? (collect($nameArr)->first() ?? '')) : '';
                return ['id' => $s->id, 'text' => $name];
            })->values();

        return response()->json(['results' => $items]);
    }

    public function searchPackages(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $locale = app()->getLocale();

        $query = Package::query()->select('id', 'name')->where('is_active', true);

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('name->ar', 'like', "%{$q}%")
                  ->orWhere('name->en', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('sort_order')->limit(10)->get()
            ->map(function ($p) use ($locale) {
                $nameArr = $p->name ?? [];
                $name = is_array($nameArr) ? ($nameArr[$locale] ?? (collect($nameArr)->first() ?? '')) : '';
                return ['id' => $p->id, 'text' => $name];
            })->values();

        return response()->json(['results' => $items]);
    }

    private function validatePromotion(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],

            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function buildPromotionPayload(Request $request, array $data): array
    {
        return [
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),

            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,

            'is_active' => $request->boolean('is_active', true),
        ];
    }
}