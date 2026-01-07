<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\Service;
use App\Models\ServiceGroupPrice;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerGroupServicePriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customer_groups.view')->only(['datatable', 'show', 'searchServices']);
        $this->middleware('can:customer_groups.edit')->only(['store', 'update', 'destroy']);
    }

    private function assertBelongsToGroup(CustomerGroup $customerGroup, ServiceGroupPrice $servicePrice): void
    {
        if ((int)$servicePrice->customer_group_id !== (int)$customerGroup->id) abort(404);
    }

    public function datatable(DataTables $datatable, Request $request, CustomerGroup $customerGroup)
    {
        $q = ServiceGroupPrice::query()
            ->where('customer_group_id', $customerGroup->id)
            ->with(['service:id,name,price,discounted_price'])
            ->select('service_group_prices.*')
            ->latest('id');

        if ($search = trim((string)$request->get('search_custom'))) {
            $q->whereHas('service', function ($x) use ($search) {
                $x->where('name->ar', 'like', "%{$search}%")
                  ->orWhere('name->en', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'active') $q->where('is_active', true);
            if ($status === 'inactive') $q->where('is_active', false);
        }

        return $datatable->eloquent($q)
            ->addColumn('service_label', function (ServiceGroupPrice $row) {
                return $row->service ? e(i18n($row->service->name)) : '—';
            })
            ->addColumn('base_price', function (ServiceGroupPrice $row) {
                return $row->service ? number_format((float)$row->service->price, 2) : '—';
            })
            ->addColumn('group_price', fn(ServiceGroupPrice $row) => number_format((float)$row->price, 2))
            ->addColumn('discounted_price', fn(ServiceGroupPrice $row) => $row->discounted_price !== null ? number_format((float)$row->discounted_price, 2) : '—')
            ->addColumn('is_active_badge', function (ServiceGroupPrice $row) {
                return $row->is_active
                    ? '<span class="badge badge-light-success">'.e(__('customer_groups.active')).'</span>'
                    : '<span class="badge badge-light-danger">'.e(__('customer_groups.inactive')).'</span>';
            })
            ->addColumn('created_at_f', fn(ServiceGroupPrice $row) => $row->created_at?->format('Y-m-d') ?? '—')
            ->addColumn('actions', function (ServiceGroupPrice $row) use ($customerGroup) {
                $editUrl = route('dashboard.customer-groups.service-prices.show', [$customerGroup->id, $row->id]);
                $updateUrl = route('dashboard.customer-groups.service-prices.update', [$customerGroup->id, $row->id]);
                $deleteUrl = route('dashboard.customer-groups.service-prices.destroy', [$customerGroup->id, $row->id]);

                return '
                    <button type="button" class="btn btn-sm btn-icon btn-light-primary btn-edit"
                        data-edit-url="'.e($editUrl).'"
                        data-update-url="'.e($updateUrl).'">
                        <i class="ki-duotone ki-pencil fs-5">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-delete"
                        data-delete-url="'.e($deleteUrl).'">
                        <i class="ki-duotone ki-trash fs-5">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                    </button>
                ';
            })
            ->rawColumns(['is_active_badge', 'actions'])
            ->make(true);
    }

    public function show(CustomerGroup $customerGroup, ServiceGroupPrice $servicePrice)
    {
        $this->assertBelongsToGroup($customerGroup, $servicePrice);

        $servicePrice->loadMissing(['service:id,name,price,discounted_price']);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $servicePrice->id,
                'service_id' => $servicePrice->service_id,
                'service_text' => $servicePrice->service ? i18n($servicePrice->service->name) : null,
                'price' => (string)$servicePrice->price,
                'discounted_price' => $servicePrice->discounted_price !== null ? (string)$servicePrice->discounted_price : null,
                'is_active' => (bool)$servicePrice->is_active,
            ]
        ]);
    }

    public function store(Request $request, CustomerGroup $customerGroup)
    {
        $data = $this->validateServicePrice($request, $customerGroup->id);

        $row = ServiceGroupPrice::create([
            'customer_group_id' => $customerGroup->id,
            'service_id' => (int)$data['service_id'],
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $data['is_active'] ?? false,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => __('customer_groups.prices.created_successfully'),
            'count' => ServiceGroupPrice::query()->where('customer_group_id', $customerGroup->id)->count(),
            'data' => ['id' => $row->id],
        ]);
    }

    public function update(Request $request, CustomerGroup $customerGroup, ServiceGroupPrice $servicePrice)
    {
        $this->assertBelongsToGroup($customerGroup, $servicePrice);

        $data = $this->validateServicePrice($request, $customerGroup->id, $servicePrice->id);

        // (اختياري) امنع تغيير الخدمة في التعديل
        if ((int)$data['service_id'] !== (int)$servicePrice->service_id) {
            return response()->json([
                'message' => __('customer_groups.prices.cannot_change_service'),
                'errors' => ['service_id' => [__('customer_groups.prices.cannot_change_service')]],
            ], 422);
        }

        $servicePrice->update([
            'price' => $data['price'],
            'discounted_price' => $data['discounted_price'] ?? null,
            'is_active' => $request->is_active !== null ? 1 : 0,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => __('customer_groups.prices.updated_successfully'),
            'count' => ServiceGroupPrice::query()->where('customer_group_id', $customerGroup->id)->count(),
        ]);
    }

    public function destroy(Request $request, CustomerGroup $customerGroup, ServiceGroupPrice $servicePrice)
    {
        $this->assertBelongsToGroup($customerGroup, $servicePrice);

        $servicePrice->delete();

        return response()->json([
            'ok' => true,
            'message' => __('customer_groups.prices.deleted_successfully'),
            'count' => ServiceGroupPrice::query()->where('customer_group_id', $customerGroup->id)->count(),
        ]);
    }

    public function searchServices(Request $request, CustomerGroup $customerGroup)
    {
        $q = trim((string)$request->get('q', ''));
        $locale = app()->getLocale();

        $query = Service::query()
            ->select('id', 'name', 'is_active')
            ->where('is_active', true);

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('name->ar', 'like', "%{$q}%")
                  ->orWhere('name->en', 'like', "%{$q}%");
            });
        }

        // استبعد الخدمات المضافة مسبقاً (لـ create)
        $exclude = ServiceGroupPrice::query()
            ->where('customer_group_id', $customerGroup->id)
            ->pluck('service_id')
            ->all();

        // عند edit: اسمح بإظهار نفس الخدمة الحالية
        $currentServiceId = (int) $request->get('current_service_id', 0);
        if ($currentServiceId > 0) {
            $exclude = array_values(array_diff($exclude, [$currentServiceId]));
        }

        if (!empty($exclude)) {
            $query->whereNotIn('id', $exclude);
        }

        $items = $query->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($s) use ($locale) {
                $name = $s->name ? ($s->name[$locale] ?? (collect($s->name)->first() ?? '')) : '';
                return ['id' => $s->id, 'text' => $name];
            })->values();

        return response()->json(['results' => $items]);
    }

    private function validateServicePrice(Request $request, int $groupId, ?int $ignoreId = null): array
    {
        $unique = 'unique:service_group_prices,service_id';
        $unique .= ',' . ($ignoreId ?? 'NULL') . ',id,customer_group_id,' . $groupId;

        return $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id', $unique],
            'price' => ['required', 'numeric', 'min:1'],
            'discounted_price' => ['required', 'numeric', 'min:0', 'lte:price'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}