<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PaymentController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {

        $this->middleware('can:payments.view')->only(['index', 'show', 'datatable']);

        $this->title = t('payments.list');
        $this->page_title = t('payments.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        return view('dashboard.payments.index');
    }

    public function datatable(DataTables $datatable, Request $request)
    {
        $query = Payment::query()
            ->with(['user', 'invoice'])
            ->select('payments.*')
            ->latest('id');

        // search (id, gateway ids, user info)
        if ($search = trim((string) $request->get('search_custom'))) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                    ->orWhere('gateway_invoice_id', 'like', "%{$search}%")
                    ->orWhere('gateway_status', 'like', "%{$search}%")
                    ->orWhere('gateway', 'like', "%{$search}%");
            })->orWhereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%"); // عدّل لو اسم العمود مختلف
            })->orWhereHas('invoice', function ($iq) use ($search) {
                $iq->where('number', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        // filters
        if ($status = $request->get('status')) {
            if (in_array($status, ['pending', 'paid', 'failed', 'cancelled', 'refunded'], true)) {
                $query->where('status', $status);
            }
        }

        if ($method = $request->get('method')) {
            if (in_array($method, ['wallet', 'credit_card', 'apple_pay', 'google_pay', 'cash', 'visa', 'stc'], true)) {
                $query->where('method', $method);
            }
        }

        if ($gateway = trim((string) $request->get('gateway'))) {
            $query->where('gateway', 'like', "%{$gateway}%");
        }

        if ($invoiceLink = $request->get('has_invoice')) {
            if ($invoiceLink === 'yes')
                $query->whereNotNull('invoice_id');
            if ($invoiceLink === 'no')
                $query->whereNull('invoice_id');
        }

        if ($payableType = $request->get('payable_type')) {
            // أمثلة: booking_payment / package_purchase / invoice_payment / refund / wallet_topup
            if (is_string($payableType) && $payableType !== '') {
                $query->where('payable_type', $payableType);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        return $datatable->eloquent($query)
            ->addColumn('user_label', function (Payment $row) {
                $name = $row->user?->name ?? '—';
                $mobile = $row->user?->mobile ?? null;
                return e($mobile ? ($name . ' - ' . $mobile) : $name);
            })
            // ->addColumn('invoice_label', function (Payment $row) {
            //     if (!$row->invoice_id) return '—';
            //     $num = $row->invoice?->number;
            //     return e($num ? ($num . ' (#' . (int)$row->invoice_id . ')') : ('#' . (int)$row->invoice_id));
            // })
            ->addColumn('payable_label', function (Payment $row) {
                if ($row->payable_type) {
                    return t('payment_purposes.' . $row->payable_type ?: '—');
                }
                return '—';
            })
            ->addColumn('method_badge', function (Payment $row) {
                $map = [
                    'wallet' => 'primary',
                    'credit_card' => 'info',
                    'apple_pay' => 'dark',
                    'google_pay' => 'dark',
                    'cash' => 'success',
                    'visa' => 'info',
                    'stc' => 'warning',
                ];
                $cls = $map[$row->method] ?? 'secondary';
                return '<span class="badge badge-light-' . $cls . '">' . e(__('payments.method.' . $row->method)) . '</span>';
            })
            ->addColumn('status_badge', function (Payment $row) {
                $map = [
                    'pending' => 'warning',
                    'paid' => 'success',
                    'failed' => 'danger',
                    'cancelled' => 'secondary',
                    'refunded' => 'info',
                ];
                $cls = $map[$row->status] ?? 'secondary';
                return '<span class="badge badge-light-' . $cls . '">' . e(__('payments.status.' . $row->status)) . '</span>';
            })
            ->addColumn('gateway_label', function (Payment $row) {
                return $row->gateway ? e($row->gateway) : '—';
            })
            ->editColumn('amount', fn(Payment $row) => number_format((float) $row->amount, 2))
            ->addColumn('paid_at_label', fn(Payment $row) => $row->paid_at ? $row->paid_at->format('Y-m-d H:i') : '—')
            ->addColumn('created_at_label', fn(Payment $row) => $row->created_at ? $row->created_at->format('Y-m-d H:i') : '—')
            ->addColumn('actions', function (Payment $row) {
                return view('dashboard.payments._actions', ['payment' => $row])->render();
            })
            ->rawColumns(['method_badge', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(Payment $payment)
    {
        $this->title = __('payments.view');
        $this->page_title = $this->title;

        view()->share([
            'title' => $this->page_title,
            'page_title' => $this->page_title,
        ]);

        $payment->loadMissing([
            'user',
            'invoice',
            // 'payable',
            'invoice.invoiceable',
        ]);

        return view('dashboard.payments.show', compact('payment'));
    }
}