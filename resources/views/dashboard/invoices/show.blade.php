@extends('base.layout.app')

@section('content')

@php
    $locale = app()->getLocale();

    $statusMap = [
        'unpaid' => 'danger',
        'paid' => 'success',
        'cancelled' => 'secondary',
        'refunded' => 'warning',
    ];
    $statusCls = $statusMap[$invoice->status] ?? 'secondary';

    $typeMap = [
        'invoice' => 'primary',
        'adjustment' => 'warning',
        'credit_note' => 'info',
    ];
    $typeCls = $typeMap[$invoice->type] ?? 'secondary';

    $currency = $invoice->currency ?: 'SAR';

    $gross = (float)$invoice->subtotal + (float)$invoice->tax;
    $discount = (float)$invoice->discount;
    $total = (float)$invoice->total;

    $meta = is_array($invoice->meta) ? $invoice->meta : (json_decode($invoice->meta ?? '[]', true) ?: []);
    $couponMeta = $meta['coupon'] ?? null;

    $invoiceableLabel = '—';
    if ($invoice->invoiceable_type && $invoice->invoiceable_id) {
        $invoiceableLabel = class_basename($invoice->invoiceable_type) . ' #' . (int)$invoice->invoiceable_id;
    }

    // helper: safe first value from array without reset() reference issue
    $firstOf = function ($arr) {
        return is_array($arr) ? (collect($arr)->first() ?? null) : null;
    };
@endphp

<div class="row g-6">

    {{-- LEFT --}}
    <div class="col-xl-8">

        {{-- HERO / HEADER --}}
        <div class="card card-flush mb-6">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">

                    <div class="d-flex align-items-center gap-4">
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-bill fs-2 text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="fw-bold fs-2">{{ __('invoices.invoice') }}</div>
                                <span class="badge badge-light-{{ $statusCls }}">{{ __('invoices.status.' . $invoice->status) }}</span>
                                @if($invoice->is_locked)
                                    <span class="badge badge-light-dark">{{ __('invoices.locked') }}</span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="fw-bold fs-3 text-gray-800" id="invoice_number_text">{{ $invoice->number }}</span>

                                <button type="button"
                                        class="btn btn-icon btn-sm btn-light"
                                        id="copy_invoice_number"
                                        data-bs-toggle="tooltip"
                                        title="{{ __('invoices.copy_number') }}">
                                    <i class="ki-duotone ki-copy fs-3">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </button>
                            </div>

                            <div class="text-muted mt-2">
                                {{ __('invoices.fields.id') }}: <span class="fw-semibold">#{{ $invoice->id }}</span>
                                <span class="mx-2">•</span>
                                {{ __('invoices.fields.version') }}: <span class="fw-semibold">{{ $invoice->version }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="text-muted">{{ __('invoices.fields.issued_at') }}</div>
                        <div class="fw-semibold fs-6">{{ $invoice->issued_at?->format('Y-m-d H:i') ?? '—' }}</div>

                        <div class="text-muted mt-3">{{ __('invoices.fields.paid_at') }}</div>
                        <div class="fw-semibold fs-6">{{ $invoice->paid_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                </div>

                <div class="separator my-6"></div>

                {{-- USER + LINKED ENTITY --}}
                <div class="row g-6">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-info">
                                    <i class="ki-duotone ki-user fs-2 text-info">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted">{{ __('invoices.fields.user') }}</div>
                                <div class="fw-bold">
                                    {{ $invoice->user?->name ?? '—' }}
                                    @if($invoice->user?->mobile)
                                        <span class="text-muted fw-semibold">- {{ $invoice->user->mobile }}</span>
                                    @endif
                                </div>
                                @if($invoice->user?->email)
                                    <div class="text-muted">{{ $invoice->user->email }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-warning">
                                    <i class="ki-duotone ki-link fs-2 text-warning">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted">{{ __('invoices.fields.invoiceable') }}</div>
                                <div class="fw-bold">{{ $invoiceableLabels }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ITEMS --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('invoices.items_title') }}</h3>
                <div class="card-toolbar text-muted">
                    {{ __('invoices.items_count') }}: <span class="fw-semibold">{{ $invoice->items->count() }}</span>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th>{{ __('invoices.item.fields.type') }}</th>
                            <th>{{ __('invoices.item.fields.title') }}</th>
                            <th>{{ __('invoices.item.fields.qty') }}</th>
                            <th>{{ __('invoices.item.fields.unit_price') }}</th>
                            <th>{{ __('invoices.item.fields.line_tax') }}</th>
                            <th class="text-end">{{ __('invoices.item.fields.line_total') }}</th>
                        </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                        @forelse($invoice->items as $item)
                            @php
                                $title = $item->title[$locale] ?? $firstOf($item->title) ?? null;

                                // fallback from itemable
                                if (!$title && $item->itemable) {
                                    if ($item->itemable_type === \App\Models\Service::class) {
                                        $title = $item->itemable->name[$locale] ?? $firstOf($item->itemable->name) ?? null;
                                    }
                                    if ($item->itemable_type === \App\Models\Product::class) {
                                        $title = method_exists($item->itemable, 'getLocalizedName')
                                            ? $item->itemable->getLocalizedName()
                                            : ($item->itemable->name[$locale] ?? $firstOf($item->itemable->name) ?? null);
                                    }
                                }

                                $desc = $item->description[$locale] ?? $firstOf($item->description) ?? null;

                                $typeBadgeMap = [
                                    'service' => 'info',
                                    'product' => 'primary',
                                    'fee' => 'warning',
                                    'custom' => 'secondary',
                                ];
                                $tb = $typeBadgeMap[$item->item_type] ?? 'secondary';
                            @endphp

                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <span class="badge badge-light-{{ $tb }}">
                                        {{ __('invoices.item.type.' . $item->item_type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $title ?: '—' }}</div>
                                    @if($desc)
                                        <div class="text-muted fw-semibold">{{ $desc }}</div>
                                    @endif
                                </td>
                                <td>{{ number_format((float)$item->qty, 2) }}</td>
                                <td>{{ number_format((float)$item->unit_price, 2) }} {{ $currency }}</td>
                                <td>{{ number_format((float)$item->line_tax, 2) }} {{ $currency }}</td>
                                <td class="text-end fw-bold">{{ number_format((float)$item->line_total, 2) }} {{ $currency }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-6">—</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAYMENTS (قراءة فقط - لو موجودة) --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('invoices.payments_title') }}</h3>
            </div>

            <div class="card-body pt-0">
                @if($invoice->relationLoaded('payments') && $invoice->payments && $invoice->payments->count())
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>#</th>
                                <th>{{ __('invoices.payment.fields.amount') }}</th>
                                <th>{{ __('invoices.payment.fields.method') }}</th>
                                <th>{{ __('invoices.payment.fields.status') }}</th>
                                <th>{{ __('invoices.payment.fields.paid_at') }}</th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                            @foreach($invoice->payments as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td class="fw-bold">{{ number_format((float)$p->amount, 2) }} {{ $currency }}</td>
                                    <td>{{ __('payment_methods.' . $p->method ?? '-') }}</td>
                                    <td>{{ __('payments.status.' . $p->status ?? '-') }}</td>
                                    <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? ($p->created_at?->format('Y-m-d H:i') ?? '—') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">—</div>
                @endif
            </div>
        </div>

    </div>

    {{-- RIGHT --}}
    <div class="col-xl-4">

        {{-- TOTALS --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('invoices.totals_title') }}</h3>
            </div>
            <div class="card-body pt-0">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('invoices.fields.subtotal') }}</div>
                    <div class="fw-bold">{{ number_format((float)$invoice->subtotal, 2) }} {{ $currency }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('invoices.fields.tax') }}</div>
                    <div class="fw-bold">{{ number_format((float)$invoice->tax, 2) }} {{ $currency }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('invoices.fields.gross_total') }}</div>
                    <div class="fw-bold">{{ number_format((float)$gross, 2) }} {{ $currency }}</div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('invoices.fields.discount') }}</div>
                    <div class="fw-bold text-danger">- {{ number_format((float)$discount, 2) }} {{ $currency }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-gray-800 fw-bold fs-5">{{ __('invoices.fields.total') }}</div>
                    <div class="fw-bolder fs-2">{{ number_format((float)$total, 2) }} {{ $currency }}</div>
                </div>

                @if($invoice->status === 'unpaid')
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mt-6">
                        <i class="ki-duotone ki-information-5 fs-2 text-warning me-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <div class="text-gray-800">{{ __('invoices.notice_unpaid_title') }}</div>
                                <div class="text-muted fs-7">{{ __('invoices.notice_unpaid_text') }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- COUPON INFO (من meta) --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('invoices.coupon_title') }}</h3>
            </div>
            <div class="card-body pt-0">
                @if(is_array($couponMeta))
                    <div class="mb-3">
                        <div class="text-muted fs-8">{{ __('invoices.coupon.code') }}</div>
                        <div class="fw-bold fs-5">{{ $couponMeta['code'] ?? '—' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted fs-8">{{ __('invoices.coupon.discount') }}</div>
                        <div class="fw-bold text-danger">- {{ number_format((float)($couponMeta['discount'] ?? 0), 2) }} {{ $currency }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted fs-8">{{ __('invoices.coupon.eligible_base') }}</div>
                        <div class="fw-semibold">{{ number_format((float)($couponMeta['eligible_base'] ?? 0), 2) }} {{ $currency }}</div>
                    </div>

                    <div class="text-muted fs-7">
                        {{ __('invoices.coupon.applied_at') }}:
                        <span class="fw-semibold">{{ $couponMeta['applied_at'] ?? '—' }}</span>
                    </div>
                @else
                    <div class="text-muted">—</div>
                @endif
            </div>
        </div>

        {{-- RELATIONS --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('invoices.relations_title') }}</h3>
            </div>
            <div class="card-body pt-0">

                <div class="mb-4">
                    <div class="text-muted fs-8">{{ __('invoices.fields.parent_invoice') }}</div>
                    @if($invoice->parent)
                        <a class="fw-bold" href="{{ route('dashboard.invoices.show', $invoice->parent->id) }}">
                            {{ $invoice->parent->number }} (#{{ $invoice->parent->id }})
                        </a>
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-8">{{ __('invoices.fields.child_invoices') }}</div>
                    @if($invoice->children && $invoice->children->count())
                        <div class="d-flex flex-column gap-2 mt-2">
                            @foreach($invoice->children as $child)
                                <a class="d-flex align-items-center justify-content-between border rounded p-3"
                                   href="{{ route('dashboard.invoices.show', $child->id) }}">
                                    <span class="fw-bold">{{ $child->number }}</span>
                                    <span class="text-muted">#{{ $child->id }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>

            </div>
        </div>

        {{-- RAW META --}}
        <div class="card card-flush d-none">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('invoices.meta_title') }}</h3>
            </div>
            <div class="card-body pt-0">
                @if(!empty($meta))
                    <pre class="bg-light rounded p-4 mb-0" style="max-height: 260px; overflow:auto;">{{ json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <div class="text-muted">—</div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('custom-script')
<script>
(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();

    const btn = document.getElementById('copy_invoice_number');
    const txt = document.getElementById('invoice_number_text');

    if (btn && txt) {
        btn.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(txt.textContent.trim());
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('invoices.copied') }}",
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (e) {
                // fallback
                const range = document.createRange();
                range.selectNodeContents(txt);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                document.execCommand('copy');
                sel.removeAllRanges();
            }
        });
    }
})();
</script>
@endpush