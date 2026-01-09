<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Employee;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;

class BookingController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        // عدّل الصلاحيات حسب نظامك
        $this->middleware('can:bookings.view')->only(['index', 'datatable', 'show']);

        $this->title = __('bookings.title');
        $this->page_title = __('bookings.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        // للفلاتر (اختياري)
        $services  = Service::query()->select('id', 'name')->where('is_active', true)->orderBy('sort_order')->get();
        $employees = Employee::query()->select('id')->get(); // إن عندك حقول اسم/موبايل اعرضهم بالواجهة
        $zones     = class_exists(Zone::class) ? Zone::query()->select('id', 'name')->orderBy('sort_order')->get() : collect();

        return view('dashboard.bookings.index', compact('services', 'employees', 'zones'));
    }

    public function datatable(DataTables $datatable, Request $request)
    {
        $query = Booking::query()
            ->select('bookings.*')
            ->with([
                'user:id,name,mobile',
                'service:id,name',
                'employee:id', // لو عندك name/mobile أضفهم هنا
            ])
            ->latest('id');

        // -------- Filters --------
        if ($status = $request->get('status')) {
            if (in_array($status, ['pending','confirmed','moving','arrived','completed','cancelled'], true)) {
                $query->where('status', $status);
            }
        }

        if ($tp = $request->get('time_period')) {
            if (in_array($tp, ['morning','evening','all'], true)) {
                $query->where('time_period', $tp);
            }
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->get('service_id'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->get('employee_id'));
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', (int) $request->get('zone_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('booking_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('booking_date', '<=', $request->get('to'));
        }

        if ($search = trim((string) $request->get('search_custom'))) {
            $query->where(function ($q) use ($search) {
                // id
                if (is_numeric($search)) {
                    $q->orWhere('bookings.id', (int) $search);
                }

                // user name/mobile
                $q->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });

                // service name (json)
                $q->orWhereHas('service', function ($s) use ($search) {
                    $s->where('name->ar', 'like', "%{$search}%")
                      ->orWhere('name->en', 'like', "%{$search}%");
                });

                // cancel reason/note
                $q->orWhere('cancel_reason', 'like', "%{$search}%")
                  ->orWhere('cancel_note', 'like', "%{$search}%");
            });
        }

        return $datatable->eloquent($query)
            ->addColumn('customer', function (Booking $row) {
                $name = $row->user?->name ?? '—';
                $mobile = $row->user?->mobile ?? null;
                return e($mobile ? "{$name} - {$mobile}" : $name);
            })
            ->addColumn('service_name', function (Booking $row) {
                return e($this->i18n($row->service?->name));
            })
            ->addColumn('schedule', function (Booking $row) {
                $d = $row->booking_date ? Carbon::parse($row->booking_date)->format('Y-m-d') : '—';
                $s = $row->start_time ? substr((string)$row->start_time, 0, 5) : '—';
                $e = $row->end_time ? substr((string)$row->end_time, 0, 5) : '—';
                $tp = $row->time_period ? __('bookings.time_period.' . $row->time_period) : '—';
                return e("{$d} • {$s} → {$e} • {$tp}");
            })
            ->addColumn('status_badge', function (Booking $row) {
                return $this->statusBadge($row->status);
            })
            ->addColumn('total', function (Booking $row) {
                return number_format((float) ($row->total_snapshot ?? 0), 2) . ' ' . e($row->currency ?? 'SAR');
            })
            ->addColumn('employee_label', function (Booking $row) {
                // عدّل لو عندك name/mobile في جدول employees
                return $row->employee_id ? ('#' . (int) $row->employee_id) : '—';
            })
            ->addColumn('actions', function (Booking $row) {
                return view('dashboard.bookings._actions', ['booking' => $row])->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Booking $booking)
    {
        $this->title = __('bookings.view');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        $booking->loadMissing([
            'user',
            'car',
            'address',
            'service.category',
            'employee',
            'packageSubscription',
            'products.product',
            'statusLogs' => fn ($q) => $q->orderByDesc('created_at'),
            'invoices' => fn ($q) => $q->orderByDesc('id')->with('payments'),
        ]);

        $latestInvoice = $booking->invoices->first();
        $latestUnpaid  = $booking->invoices->firstWhere('status', 'unpaid');

        return view('dashboard.bookings.show', compact('booking', 'latestInvoice', 'latestUnpaid'));
    }

    // ================= Helpers =================

    private function i18n($json, string $fallback = '—'): string
    {
        if (!$json) return $fallback;
        $locale = app()->getLocale();

        if (is_array($json)) {
            return (string) ($json[$locale] ?? (collect($json)->first() ?? $fallback));
        }

        return (string) $json;
    }

    private function statusBadge(?string $status): string
    {
        $status = $status ?: 'pending';

        $map = [
            'pending'   => 'badge-light-warning',
            'confirmed' => 'badge-light-primary',
            'moving'    => 'badge-light-info',
            'arrived'   => 'badge-light-primary',
            'completed' => 'badge-light-success',
            'cancelled' => 'badge-light-danger',
        ];

        $class = $map[$status] ?? 'badge-light';

        return '<span class="badge ' . e($class) . '">' . e(__('bookings.status.' . $status)) . '</span>';
    }
}