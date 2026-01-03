<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageSubscription;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PackageSubscriptionController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {

        $this->middleware('can:package_subscriptions.view')->only(['index', 'show']);
        $this->middleware('can:package_subscriptions.edit')->only(['edit', 'update']);
        $this->middleware('can:package_subscriptions.delete')->only(['destroy']);

        $this->title = t('package_subscriptions.list');
        $this->page_title = t('package_subscriptions.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = PackageSubscription::query()
                ->with(['user:id,name,mobile', 'package:id,name'])
                ->select('package_subscriptions.*');

            // 🔍 البحث العام (اسم العميل / موبايل / اسم الباقة)
            if ($search = $request->get('search_custom')) {
                $search = trim($search);

                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })->orWhereHas('package', function ($q3) use ($search) {
                        $q3->where('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%");
                    });
                });
            }

            // 🎛 فلتر الحالة
            if ($status = $request->get('status')) {
                if (in_array($status, ['active', 'expired', 'cancelled'])) {
                    $query->where('status', $status);
                }
            }

            // 🎛 فلتر الباقة
            if ($packageId = $request->get('package_id')) {
                $query->where('package_id', $packageId);
            }

            // 🎛 فلتر تاريخ البداية/النهاية (اختياري)
            if ($dateFrom = $request->get('starts_from')) {
                $query->whereDate('starts_at', '>=', $dateFrom);
            }

            if ($dateTo = $request->get('ends_to')) {
                $query->whereDate('ends_at', '<=', $dateTo);
            }

            return $datatable->eloquent($query)
                ->editColumn('id', fn(PackageSubscription $row) => $row->id)
                ->addColumn('customer_name', function (PackageSubscription $row) {
                    return $row->user?->name ?? '—';
                })
                ->addColumn('customer_mobile', function (PackageSubscription $row) {
                    return $row->user?->mobile ?? '—';
                })
                ->addColumn('package_name', function (PackageSubscription $row) {
                    if (!$row->package) {
                        return '—';
                    }

                    $locale = app()->getLocale();
                    $name = $row->package->name ?? null;

                    if (is_array($name)) {
                        return e($name[$locale] ?? reset($name) ?? '');
                    }

                    return e($name);
                })
                ->addColumn('status_badge', function (PackageSubscription $row) {
                    return match ($row->status) {
                        'active' => '<span class="badge badge-light-success">' . e(__('package_subscriptions.status_active')) . '</span>',
                        'expired' => '<span class="badge badge-light-warning">' . e(__('package_subscriptions.status_expired')) . '</span>',
                        'cancelled' => '<span class="badge badge-light-danger">' . e(__('package_subscriptions.status_cancelled')) . '</span>',
                        default => '<span class="badge badge-light-secondary">' . e($row->status) . '</span>',
                    };
                })
                ->addColumn('period', function (PackageSubscription $row) {
                    $start = optional($row->starts_at)->format('Y-m-d');
                    $end = optional($row->ends_at)->format('Y-m-d');

                    return $start && $end ? "{$start} → {$end}" : '—';
                })
                ->editColumn('remaining_washes', function (PackageSubscription $row) {
                    return $row->remaining_washes . ' / ' . $row->total_washes_snapshot;
                })
                ->editColumn('final_price_snapshot', function (PackageSubscription $row) {
                    return number_format($row->final_price_snapshot, 2);
                })
                ->editColumn('purchased_at', function (PackageSubscription $row) {
                    return optional($row->purchased_at)->format('Y-m-d H:i');
                })
                ->addColumn('actions', function (PackageSubscription $row) {
                    return view('dashboard.package_subscriptions._actions', [
                        'subscription' => $row,
                    ])->render();
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        // البيانات اللازمة للفلاتر
        $packages = Package::select('id', 'name')->orderBy('sort_order')->get();

        return view('dashboard.package_subscriptions.index', compact('packages'));
    }

    public function edit(PackageSubscription $packageSubscription)
    {
        // صفحة إدارة الاشتراك (لاحقاً نضيف تفاصيلها)
        return view('dashboard.package_subscriptions.edit', [
            'subscription' => $packageSubscription->load(['user', 'package']),
        ]);
    }

    public function update(Request $request, PackageSubscription $packageSubscription)
    {
        // ✅ فلترة وحماية القيم
        $data = $request->validate([
            'status' => ['required', 'in:active,expired,cancelled'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'remaining_washes' => [
                'required',
                'integer',
                'min:0',
                'max:' . $packageSubscription->total_washes_snapshot,
            ],
            'purchased_at' => ['nullable', 'date'],
        ]);

        $payload = [
            'status' => $data['status'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'remaining_washes' => $data['remaining_washes'],
            'purchased_at' => $data['purchased_at'] ?? null,
        ];

        $packageSubscription->update($payload);

        // 🔁 لو الطلب AJAX (من صفحة التعديل الحالية)
        if ($request->ajax()) {
            return response()->json([
                'message' => __('package_subscriptions.updated_successfully', [], app()->getLocale())
                    ?? (app()->getLocale() === 'ar'
                        ? 'تم تحديث الاشتراك بنجاح.'
                        : 'Subscription updated successfully.'),
                'redirect' => route('dashboard.package-subscriptions.show', $packageSubscription->id),
            ]);
        }

        // 🔁 لو فورم عادي (بدون AJAX)
        return redirect()
            ->route('dashboard.package-subscriptions.show', $packageSubscription->id)
            ->with('success', __('package_subscriptions.updated_successfully', [], app()->getLocale())
                ?? (app()->getLocale() === 'ar'
                    ? 'تم تحديث الاشتراك بنجاح.'
                    : 'Subscription updated successfully.'));
    }

    public function show(PackageSubscription $packageSubscription)
    {
        return view('dashboard.package_subscriptions.show', [
            'subscription' => $packageSubscription->load(['user', 'package']),
        ]);
    }
}
