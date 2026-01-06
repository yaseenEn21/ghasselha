<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class InvoiceController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:invoices.view')->only(['index', 'show', 'datatable']);

        $this->title = t('invoices.list');
        $this->page_title = t('invoices.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        return view('dashboard.invoices.index');
    }

    public function datatable(DataTables $datatable, Request $request)
    {
        $query = Invoice::query()
            ->with(['user'])
            ->select('invoices.*')
            ->latest('id');

        // search (invoice number + user name/mobile/email)
        if ($search = trim((string) $request->get('search_custom'))) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            })->orWhereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhere('mobile', 'like', "%{$search}%"); // عدّل لو اسم العمود مختلف
            });
        }

        // filters
        if ($status = $request->get('status')) {
            if (in_array($status, ['unpaid', 'paid', 'cancelled', 'refunded'], true)) {
                $query->where('status', $status);
            }
        }

        if ($type = $request->get('type')) {
            if (in_array($type, ['invoice', 'adjustment', 'credit_note'], true)) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('issued_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('issued_at', '<=', $request->get('to'));
        }

        if ($locked = $request->get('locked')) {
            if ($locked === 'yes') $query->where('is_locked', true);
            if ($locked === 'no')  $query->where('is_locked', false);
        }

        return $datatable->eloquent($query)
            ->addColumn('user_label', function (Invoice $row) {
                $name = $row->user?->name ?? '—';
                $mobile = $row->user?->mobile ?? null;
                return e($mobile ? ($name . ' - ' . $mobile) : $name);
            })
            ->addColumn('type_badge', function (Invoice $row) {
                $map = [
                    'invoice' => 'primary',
                    'adjustment' => 'warning',
                    'credit_note' => 'info',
                ];
                $cls = $map[$row->type] ?? 'secondary';
                return '<span class="badge badge-light-' . $cls . '">' . e(__('invoices.type.' . $row->type)) . '</span>';
            })
            ->addColumn('status_badge', function (Invoice $row) {
                $map = [
                    'unpaid' => 'danger',
                    'paid' => 'success',
                    'cancelled' => 'secondary',
                    'refunded' => 'warning',
                ];
                $cls = $map[$row->status] ?? 'secondary';
                return '<span class="badge badge-light-' . $cls . '">' . e(__('invoices.status.' . $row->status)) . '</span>';
            })
            ->addColumn('locked_badge', function (Invoice $row) {
                if (!$row->is_locked) return '—';
                return '<span class="badge badge-light-dark">' . e(__('invoices.locked')) . '</span>';
            })
            ->editColumn('subtotal', fn (Invoice $row) => number_format((float)$row->subtotal, 2))
            ->editColumn('discount', fn (Invoice $row) => number_format((float)$row->discount, 2))
            ->editColumn('tax', fn (Invoice $row) => number_format((float)$row->tax, 2))
            ->editColumn('total', fn (Invoice $row) => number_format((float)$row->total, 2))
            ->addColumn('issued_at_label', fn (Invoice $row) => $row->issued_at ? $row->issued_at->format('Y-m-d H:i') : '—')
            ->addColumn('paid_at_label', fn (Invoice $row) => $row->paid_at ? $row->paid_at->format('Y-m-d H:i') : '—')
            ->addColumn('invoiceable_label', function (Invoice $row) {
                if (!$row->invoiceable_type || !$row->invoiceable_id) return '—';
                $short = class_basename($row->invoiceable_type);
                return e($short . ' #' . (int)$row->invoiceable_id);
            })
            ->addColumn('actions', function (Invoice $row) {
                return view('dashboard.invoices._actions', ['invoice' => $row])->render();
            })
            ->rawColumns(['type_badge', 'status_badge', 'locked_badge', 'actions'])
            ->make(true);
    }

    public function show(Invoice $invoice)
    {
        $this->title = __('invoices.view');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        $invoice->loadMissing([
            'user',
            'items',
            'items.itemable',
            'parent',
            'children',
            'payments', // لو موجودة عندك
            'invoiceable',
        ]);

        return view('dashboard.invoices.show', compact('invoice'));
    }
}