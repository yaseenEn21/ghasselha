<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionCoupon;
use App\Models\PromotionCouponRedemption;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PromotionCouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:promotions.view')->only(['index', 'datatable', 'redemptions', 'redemptionsDatatable']);
        $this->middleware('can:promotions.edit')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    private function assertCouponBelongsToPromotion(Promotion $promotion, PromotionCoupon $coupon): void
    {
        if ((int) $coupon->promotion_id !== (int) $promotion->id)
            abort(404);
    }

    public function index(Promotion $promotion)
    {
        view()->share([
            'title' => __('promotions.coupons.title'),
            'page_title' => __('promotions.coupons.title'),
        ]);

        return view('dashboard.promotions.coupons.index', compact('promotion'));
    }

    public function datatable(DataTables $datatable, Request $request, Promotion $promotion)
    {
        $query = PromotionCoupon::query()
            ->where('promotion_id', $promotion->id)
            ->select('promotion_coupons.*')
            ->latest('id');

        if ($search = trim((string) $request->get('search_custom'))) {
            $query->where('code', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            if ($status === 'active')
                $query->where('is_active', true);
            if ($status === 'inactive')
                $query->where('is_active', false);
        }

        return $datatable->eloquent($query)
            ->addColumn('period', function (PromotionCoupon $row) {
                $s = $row->starts_at ? $row->starts_at->format('Y-m-d') : '—';
                $e = $row->ends_at ? $row->ends_at->format('Y-m-d') : '—';
                return e($s . ' → ' . $e);
            })
            ->addColumn('discount_label', function (PromotionCoupon $row) {
                if ($row->discount_type === 'percent') {
                    $v = rtrim(rtrim(number_format((float) $row->discount_value, 2), '0'), '.');
                    return e($v . '%');
                }
                return e(number_format((float) $row->discount_value, 2) . ' SAR');
            })
            ->addColumn('applies_to_label', fn(PromotionCoupon $row) => e(__('promotions.applies_to_' . $row->applies_to)))
            ->addColumn('is_active_badge', function (PromotionCoupon $row) {
                return $row->is_active
                    ? '<span class="badge badge-light-success">' . e(__('promotions.active')) . '</span>'
                    : '<span class="badge badge-light-danger">' . e(__('promotions.inactive')) . '</span>';
            })
            ->addColumn('actions', function (PromotionCoupon $row) use ($promotion) {
                return view('dashboard.promotions.coupons._actions', [
                    'promotion' => $promotion,
                    'coupon' => $row,
                ])->render();
            })
            ->rawColumns(['is_active_badge', 'actions'])
            ->make(true);
    }

    public function create(Promotion $promotion)
    {
        view()->share([
            'title' => __('promotions.coupons.create'),
            'page_title' => __('promotions.coupons.create'),
        ]);

        return view('dashboard.promotions.coupons.create', compact('promotion'));
    }

    public function store(Request $request, Promotion $promotion)
    {
        $data = $this->validateCoupon($request);

        $coupon = PromotionCoupon::create(array_merge(
            $this->buildCouponPayload($request, $data),
            ['promotion_id' => $promotion->id]
        ));

        $this->syncScopeRelations($coupon, $request, $data);

        return $request->ajax()
            ? response()->json([
                'message' => __('promotions.coupons.created_successfully'),
                'redirect' => route('dashboard.promotions.coupons.index', $promotion->id),
                'data' => ['id' => $coupon->id],
            ])
            : redirect()->route('dashboard.promotions.coupons.index', $promotion->id)->with('success', __('promotions.coupons.created_successfully'));
    }

    public function edit(Promotion $promotion, PromotionCoupon $coupon)
    {
        $this->assertCouponBelongsToPromotion($promotion, $coupon);

        $coupon->load(['services:id,name', 'packages:id,name']);

        view()->share([
            'title' => __('promotions.coupons.edit'),
            'page_title' => __('promotions.coupons.edit'),
        ]);

        return view('dashboard.promotions.coupons.edit', compact('promotion', 'coupon'));
    }

    public function update(Request $request, Promotion $promotion, PromotionCoupon $coupon)
    {
        $this->assertCouponBelongsToPromotion($promotion, $coupon);

        $data = $this->validateCoupon($request, $coupon->id);

        $coupon->update($this->buildCouponPayload($request, $data));

        $this->syncScopeRelations($coupon, $request, $data);

        return $request->ajax()
            ? response()->json([
                'message' => __('promotions.coupons.updated_successfully'),
                'redirect' => route('dashboard.promotions.coupons.index', $promotion->id),
                'data' => ['id' => $coupon->id],
            ])
            : redirect()->route('dashboard.promotions.coupons.index', $promotion->id)->with('success', __('promotions.coupons.updated_successfully'));
    }

    public function destroy(Request $request, Promotion $promotion, PromotionCoupon $coupon)
    {
        // $this->assertCouponBelongsToPromotion($promotion, $coupon);

        $coupon->delete();

        return $request->ajax()
            ? response()->json(['message' => __('promotions.coupons.deleted_successfully')])
            : redirect()->route('dashboard.promotions.coupons.index', $promotion->id)->with('success', __('promotions.coupons.deleted_successfully'));
    }

    public function redemptions(Promotion $promotion, PromotionCoupon $coupon)
    {
        $this->assertCouponBelongsToPromotion($promotion, $coupon);

        // تحميل الحملة + احصائيات الاستخدام
        $coupon->load('promotion')
            ->loadCount([
                'redemptions as redemptions_applied_count' => fn($q) => $q->where('status', 'applied'),
                'redemptions as redemptions_voided_count' => fn($q) => $q->where('status', 'voided'),
            ])
            ->loadSum([
                'redemptions as discount_applied_sum' => fn($q) => $q->where('status', 'applied'),
            ], 'discount_amount');

        // تحميل الخدمات/الباقات (إذا عندك علاقات coupons->services / coupons->packages)
        if (method_exists($coupon, 'services')) {
            $coupon->load(['services:id,name']);
        }
        if (method_exists($coupon, 'packages')) {
            $coupon->load(['packages:id,name']);
        }

        view()->share([
            'title' => __('promotions.coupons.redemptions_title'),
            'page_title' => __('promotions.coupons.redemptions_title'),
        ]);

        return view('dashboard.promotions.coupons.redemptions', compact('promotion', 'coupon'));
    }

    public function redemptionsDatatable(DataTables $datatable, Request $request, Promotion $promotion, PromotionCoupon $coupon)
    {
        $this->assertCouponBelongsToPromotion($promotion, $coupon);

        $query = PromotionCouponRedemption::query()
            ->where('promotion_coupon_id', $coupon->id)
            ->with(['invoice', 'coupon', 'user'])
            ->select('promotion_coupon_redemptions.*')
            ->latest('id');

        if ($status = $request->get('status')) {
            if (in_array($status, ['applied', 'voided'], true))
                $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('search_custom'))) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        return $datatable->eloquent($query)
            ->addColumn('user_label', function (PromotionCouponRedemption $row) {
                $name = $row->user?->name ?? '—';
                $mobile = $row->user?->mobile ?? null;
                return e($mobile ? ($name . ' - ' . $mobile) : $name);
            })
            ->addColumn('invoice_label', fn(PromotionCouponRedemption $row) => $row->invoice_id ? ('#' . (int) $row->invoice_id) : '—')
            ->addColumn('status_badge', function (PromotionCouponRedemption $row) {
                return $row->status === 'applied'
                    ? '<span class="badge badge-light-success">' . e(__('promotions.coupons.status_applied')) . '</span>'
                    : '<span class="badge badge-light-danger">' . e(__('promotions.coupons.status_voided')) . '</span>';
            })
            ->editColumn('discount_amount', fn(PromotionCouponRedemption $row) => number_format((float) $row->discount_amount, 2))
            ->editColumn('applied_at', fn(PromotionCouponRedemption $row) => $row->applied_at ? $row->applied_at->format('Y-m-d H:i') : '—')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    // ===== helpers =====

    private function validateCoupon(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:promotion_coupons,code';
        if ($ignoreId) {
            $unique .= ',' . $ignoreId;
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:30', $unique],
            'is_active' => ['nullable', 'boolean'],

            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],

            'applies_to' => ['required', 'in:service,package,both'],
            'apply_all_services' => ['nullable', 'boolean'],
            'apply_all_packages' => ['nullable', 'boolean'],

            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],

            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['integer', 'exists:packages,id'],

            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],

            'usage_limit_total' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],

            'min_invoice_total' => ['nullable', 'numeric', 'min:0'],

            // meta optional (لو عندك inputs إضافية مستقبلًا)
            'meta' => ['nullable'],
        ]);
    }

    private function buildCouponPayload(Request $request, array $data): array
    {
        // 1) meta الأصلي (إن وجد)
        $meta = [];
        $rawMeta = $request->input('meta');

        if (is_string($rawMeta) && trim($rawMeta) !== '') {
            $decoded = json_decode($rawMeta, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $meta = $decoded;
            }
        } elseif (is_array($rawMeta)) {
            $meta = $rawMeta;
        }

        // 2) normalize ids
        $serviceIds = $request->input('service_ids', []);
        $packageIds = $request->input('package_ids', []);

        $serviceIds = is_array($serviceIds) ? array_values(array_filter(array_map('intval', $serviceIds))) : [];
        $packageIds = is_array($packageIds) ? array_values(array_filter(array_map('intval', $packageIds))) : [];

        $appliesTo = $data['applies_to'];
        $applyAllServices = $request->boolean('apply_all_services', false);
        $applyAllPackages = $request->boolean('apply_all_packages', false);

        // 3) تنظيف حسب applies_to
        $useServices = in_array($appliesTo, ['service', 'both'], true);
        $usePackages = in_array($appliesTo, ['package', 'both'], true);

        if (!$useServices || $applyAllServices) {
            $serviceIds = [];
        }

        if (!$usePackages || $applyAllPackages) {
            $packageIds = [];
        }

        // 4) دمج الـ ids داخل meta (مع الحفاظ على أي meta إضافي)
        $meta['service_ids'] = $serviceIds;
        $meta['package_ids'] = $packageIds;

        // 5) إذا meta صار فاضي تمامًا -> نخزنه null (اختياري)
        $metaForDb = !empty($meta) ? $meta : null;

        return [
            'code' => strtoupper(trim((string) $data['code'])),
            'is_active' => $request->boolean('is_active', true),

            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,

            'applies_to' => $appliesTo,
            'apply_all_services' => $applyAllServices,
            'apply_all_packages' => $applyAllPackages,

            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'max_discount' => $data['max_discount'] ?? null,

            'usage_limit_total' => $data['usage_limit_total'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,

            'min_invoice_total' => $data['min_invoice_total'] ?? null,
            'meta' => $metaForDb,
        ];
    }

    private function syncScopeRelations(PromotionCoupon $coupon, Request $request, array $data): void
    {
        $appliesTo = $data['applies_to'];

        $useServices = in_array($appliesTo, ['service', 'both'], true);
        $usePackages = in_array($appliesTo, ['package', 'both'], true);

        if (!$useServices || $request->boolean('apply_all_services', false)) {
            $coupon->services()->detach();
        } else {
            $ids = $request->input('service_ids', []);
            $ids = is_array($ids) ? array_filter($ids) : [];
            $coupon->services()->sync($ids);
        }

        if (!$usePackages || $request->boolean('apply_all_packages', false)) {
            $coupon->packages()->detach();
        } else {
            $ids = $request->input('package_ids', []);
            $ids = is_array($ids) ? array_filter($ids) : [];
            $coupon->packages()->sync($ids);
        }
    }
}