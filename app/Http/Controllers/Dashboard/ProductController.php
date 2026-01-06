<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BookingProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        // app()->setLocale('en');

        $this->middleware('can:products.view')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['create', 'store']);
        $this->middleware('can:products.edit')->only(['edit', 'update']);
        $this->middleware('can:products.delete')->only(['destroy']);

        $this->title = t('products.list');
        $this->page_title = t('products.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = Product::query()
                ->with('category')
                ->select('products.*')
                ->orderBy('sort_order');

            if ($search = trim((string) $request->get('search_custom'))) {
                $query->where(function ($q) use ($search) {
                    $q->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            }

            if ($categoryId = $request->get('category_id')) {
                $query->where('product_category_id', $categoryId);
            }

            if ($status = $request->get('status')) {
                if ($status === 'active')
                    $query->where('is_active', true);
                if ($status === 'inactive')
                    $query->where('is_active', false);
            }

            return $datatable->eloquent($query)
                ->editColumn('name', function (Product $row) {
                    return e($row->getLocalizedName());
                })
                ->addColumn('category_name', function (Product $row) {
                    if (!$row->category)
                        return '—';
                    $locale = app()->getLocale();
                    $n = $row->category->name ?? [];
                    return e($n[$locale] ?? (reset($n) ?: ''));
                })
                ->addColumn('is_active_badge', function (Product $row) {
                    if ($row->is_active) {
                        return '<span class="badge badge-light-success">' . e(__('products.active')) . '</span>';
                    }
                    return '<span class="badge badge-light-danger">' . e(__('products.inactive')) . '</span>';
                })
                ->editColumn('price', fn(Product $row) => number_format((float) $row->price, 2))
                ->editColumn('discounted_price', function (Product $row) {
                    return $row->discounted_price !== null ? number_format((float) $row->discounted_price, 2) : '—';
                })
                ->editColumn('max_qty_per_booking', fn(Product $row) => $row->max_qty_per_booking ?? '—')
                ->editColumn('created_at', fn(Product $row) => optional($row->created_at)->format('Y-m-d'))
                ->addColumn('actions', function (Product $row) {
                    return view('dashboard.products._actions', ['product' => $row])->render();
                })
                ->rawColumns(['is_active_badge', 'actions'])
                ->make(true);
        }

        $categories = ProductCategory::select('id', 'name')->get();

        return view('dashboard.products.index', compact('categories'));
    }

    public function create()
    {
        $this->title = t('products.create_new');
        $this->page_title = $this->title;

        view()->share(['title' => $this->page_title, 'page_title' => $this->page_title]);

        $categories = ProductCategory::select('id', 'name')->where('is_active', true)->orderBy('sort_order')->get();

        return view('dashboard.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_category_id' => ['nullable', 'exists:product_categories,id'],

            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],

            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],

            'max_qty_per_booking' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],

            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $desiredPosition = $data['sort_order'] ?? null;

        if ($desiredPosition) {
            Product::where('sort_order', '>=', $desiredPosition)->increment('sort_order');
        } else {
            $max = Product::max('sort_order') ?? 0;
            $desiredPosition = $max + 1;
        }

        $payload = [
            'product_category_id' => $data['product_category_id'] ?? null,
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'max_qty_per_booking' => $data['max_qty_per_booking'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $desiredPosition,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        $product = Product::create($payload);

        if ($request->hasFile('image_ar')) {
            $product->addMediaFromRequest('image_ar')->toMediaCollection('image_ar');
        }
        if ($request->hasFile('image_en')) {
            $product->addMediaFromRequest('image_en')->toMediaCollection('image_en');
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => __('products.created_successfully'),
                'redirect' => route('dashboard.products.index'),
                'data' => ['id' => $product->id],
            ]);
        }

        return redirect()->route('dashboard.products.index')->with('success', __('products.created_successfully'));
    }


    public function show(Product $product, Request $request)
    {
        [$fromDate, $toDate, $from, $to] = $this->resolveDateRange($request);

        $base = DB::table('booking_products')
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $totalQty = (int) $base->sum('qty');
        $totalSales = (float) $base->sum('line_total');

        // الربح (اختياري): إذا عندك cost مستقبلاً
        $profit = null;
        if (isset($product->cost) && $product->cost !== null) {
            $profit = $totalSales - ($totalQty * (float) $product->cost);
        }

        return view('dashboard.products.show', compact(
            'product',
            'from',
            'to',
            'totalQty',
            'totalSales',
            'profit'
        ));
    }

    public function edit(Product $product)
    {
        $this->title = t('products.edit');
        $this->page_title = $this->title;

        view()->share(['title' => $this->page_title, 'page_title' => $this->page_title]);

        $categories = ProductCategory::select('id', 'name')->where('is_active', true)->orderBy('sort_order')->get();

        return view('dashboard.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'product_category_id' => ['nullable', 'exists:product_categories,id'],

            'name.ar' => ['required', 'string', 'max:190'],
            'name.en' => ['nullable', 'string', 'max:190'],

            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'price' => ['required', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],

            'max_qty_per_booking' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],

            'image_ar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'image_en' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],

            'image_ar_remove' => ['nullable'],
            'image_en_remove' => ['nullable'],
        ]);

        $oldPosition = $product->sort_order ?? (Product::max('sort_order') + 1);
        $newPosition = $data['sort_order'] ?? $oldPosition;

        if ($newPosition != $oldPosition) {
            if ($newPosition < $oldPosition) {
                Product::where('id', '!=', $product->id)
                    ->whereBetween('sort_order', [$newPosition, $oldPosition - 1])
                    ->increment('sort_order');
            } else {
                Product::where('id', '!=', $product->id)
                    ->whereBetween('sort_order', [$oldPosition + 1, $newPosition])
                    ->decrement('sort_order');
            }
        }

        $payload = [
            'product_category_id' => $data['product_category_id'] ?? null,
            'name' => $request->input('name', []),
            'description' => $request->input('description', []),
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'max_qty_per_booking' => $data['max_qty_per_booking'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $newPosition,
            'updated_by' => auth()->id(),
        ];

        $product->update($payload);

        if ($request->filled('image_ar_remove')) {
            $product->clearMediaCollection('image_ar');
        }
        if ($request->filled('image_en_remove')) {
            $product->clearMediaCollection('image_en');
        }

        if ($request->hasFile('image_ar')) {
            $product->addMediaFromRequest('image_ar')->toMediaCollection('image_ar');
        }
        if ($request->hasFile('image_en')) {
            $product->addMediaFromRequest('image_en')->toMediaCollection('image_en');
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => __('products.updated_successfully'),
                'redirect' => route('dashboard.products.index'),
                'data' => ['id' => $product->id],
            ]);
        }

        return redirect()->route('dashboard.products.index')->with('success', __('products.updated_successfully'));
    }

    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        if ($request->ajax()) {
            return response()->json(['message' => __('products.deleted_successfully')]);
        }

        return redirect()->route('dashboard.products.index')->with('success', __('products.deleted_successfully'));
    }

    public function salesLinesDatatable(Product $product, DataTables $datatable, Request $request)
    {
        [$fromDate, $toDate] = $this->resolveDateRange($request);

        $query = DB::table('booking_products')
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select([
                'id',
                'booking_id',
                'qty',
                'unit_price_snapshot',
                'line_total',
                'created_at'
            ]);

        return $datatable->query($query)
            ->editColumn('booking_id', fn($row) => '#' . $row->booking_id)
            ->editColumn('unit_price_snapshot', fn($row) => number_format((float) $row->unit_price_snapshot, 2))
            ->editColumn('line_total', fn($row) => number_format((float) $row->line_total, 2))
            ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('Y-m-d'))
            ->make(true);
    }

    public function salesStats(Product $product, Request $request)
    {
        [$fromDate, $toDate] = $this->resolveDateRange($request);

        $base = DB::table('booking_products')
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $totalQty = (int) $base->sum('qty');
        $totalSales = (float) $base->sum('line_total');

        $profit = null;
        if (isset($product->cost) && $product->cost !== null) {
            $profit = $totalSales - ($totalQty * (float) $product->cost);
        }

        return response()->json([
            'total_qty' => $totalQty,
            'total_sales' => round($totalSales, 2),
            'profit' => $profit === null ? null : round((float) $profit, 2),
        ]);
    }

    private function resolveDateRange(Request $request): array
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

        // إرجاع سترينج للقيم الافتراضية بالinputs
        return [$fromDate, $toDate, $fromDate->toDateString(), $toDate->toDateString()];
    }
}