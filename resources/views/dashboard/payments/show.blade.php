@extends('base.layout.app')

@section('content')

@section('top-btns')
   {{-- print --}}
@endsection

@php
    /** @var \App\Models\Payment $payment */
    $currency = $payment->currency ?: 'SAR';

    $statusMap = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
        'cancelled' => 'secondary',
        'refunded' => 'info',
    ];
    $statusCls = $statusMap[$payment->status] ?? 'secondary';

    $methodMap = [
        'wallet' => 'primary',
        'credit_card' => 'info',
        'apple_pay' => 'dark',
        'google_pay' => 'dark',
        'cash' => 'success',
        'visa' => 'info',
        'stc' => 'warning',
    ];
    $methodCls = $methodMap[$payment->method] ?? 'secondary';

    $meta = is_array($payment->meta) ? $payment->meta : (json_decode($payment->meta ?? '[]', true) ?: []);
    $gatewayRaw = is_array($payment->gateway_raw) ? $payment->gateway_raw : (json_decode($payment->gateway_raw ?? '[]', true) ?: []);

    $invoiceLabel = '—';
    if ($payment->invoice_id) {
        $invoiceLabel = $payment->invoice?->number
            ? ($payment->invoice->number . ' (#' . (int)$payment->invoice_id . ')')
            : ('#' . (int)$payment->invoice_id);
    }

    $payableLabel = '—';
    $payableId = null;
    $payableLocal = null;
    if ($payment->payable_type && $payment->payable_id) {
        $payableLabel = $payment->payable_type . ' #' . (int)$payment->payable_id;
        $payableLocal = $payment->payable_type;
        $payableId = $payment->payable_id;
    } elseif ($payment->payable_type) {
        $payableLabel = $payment->payable_type;
    }
@endphp

<div class="row g-6">

    {{-- LEFT --}}
    <div class="col-xl-8">

        {{-- HERO --}}
        <div class="card card-flush mb-6">
            <div class="card-body">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-wallet fs-2 text-success">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="fw-bold fs-2">{{ __('payments.payment') }}</div>
                                <span class="badge badge-light-{{ $statusCls }}">{{ __('payments.status.' . $payment->status) }}</span>
                                <span class="badge badge-light-{{ $methodCls }}">{{ __('payments.method.' . $payment->method) }}</span>
                                @if($payment->gateway)
                                    <span class="badge badge-light-dark">{{ $payment->gateway }}</span>
                                @endif
                            </div>

                            <div class="text-muted mt-2">
                                {{ __('payments.fields.id') }}: <span class="fw-semibold">#{{ $payment->id }}</span>
                                <span class="mx-2">•</span>
                                {{ __('payments.fields.created_at') }}:
                                <span class="fw-semibold">{{ $payment->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="text-muted">{{ __('payments.fields.amount') }}</div>
                        <div class="fw-bolder fs-2">{{ number_format((float)$payment->amount, 2) }} {{ $currency }}</div>

                        <div class="text-muted mt-3">{{ __('payments.fields.paid_at') }}</div>
                        <div class="fw-semibold fs-6">{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                </div>

                <div class="separator my-6"></div>

                {{-- USER / LINKS --}}
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
                                <div class="text-muted">{{ __('payments.fields.user') }}</div>
                                <div class="fw-bold">
                                    {{ $payment->user?->name ?? '—' }}
                                    @if($payment->user?->mobile)
                                        <span class="text-muted fw-semibold">- {{ $payment->user->mobile }}</span>
                                    @endif
                                </div>
                                @if($payment->user?->email)
                                    <div class="text-muted">{{ $payment->user->email }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-bill fs-2 text-primary">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted">{{ __('payments.fields.invoice') }}</div>

                                @if($payment->invoice_id)
                                    <div class="fw-bold">
                                        <a href="{{ route('dashboard.invoices.show', $payment->invoice_id) }}">
                                            {{ $invoiceLabel }}
                                        </a>
                                    </div>
                                @else
                                    <div class="text-muted">—</div>
                                @endif

                                {{-- <div class="text-muted mt-1">
                                    {{ __('payments.fields.payable') }}: <span class="fw-semibold">{{ $payableLabel }}</span>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                @if($payment->status === 'failed')
                    <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-4 mt-6">
                        <i class="ki-duotone ki-information-5 fs-2 text-danger me-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="fw-semibold">
                            <div class="text-gray-800">{{ __('payments.notice_failed_title') }}</div>
                            <div class="text-muted fs-7">{{ __('payments.notice_failed_text') }}</div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- GATEWAY DETAILS --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('payments.gateway_title') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="row g-6">
                    <div class="col-md-4">
                        <div class="text-muted fs-8">{{ __('payments.fields.gateway') }}</div>
                        <div class="fw-bold">{{ $payment->gateway ?: '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted fs-8">{{ __('payments.fields.gateway_status') }}</div>
                        <div class="fw-bold">{{ $payment->gateway_status ?: '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted fs-8">{{ __('payments.fields.gateway_payment_id') }}</div>
                        <div class="fw-bold">{{ $payment->gateway_payment_id ?: '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted fs-8">{{ __('payments.fields.gateway_invoice_id') }}</div>
                        <div class="fw-bold">{{ $payment->gateway_invoice_id ?: '—' }}</div>
                    </div>

                    <div class="col-md-8">
                        <div class="text-muted fs-8">{{ __('payments.fields.gateway_transaction_url') }}</div>
                        @if($payment->gateway_transaction_url)
                            <a href="{{ $payment->gateway_transaction_url }}" target="_blank" class="fw-bold">
                                {{ __('payments.open_transaction') }}
                            </a>
                        @else
                            <div class="text-muted">—</div>
                        @endif
                    </div>
                </div>

                <div class="separator my-6"></div>

                <div class="accordion" id="gatewayRawAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingGatewayRaw">
                            <button class="accordion-button collapsed fw-bold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseGatewayRaw"
                                    aria-expanded="false" aria-controls="collapseGatewayRaw">
                                {{ __('payments.gateway_raw_title') }}
                            </button>
                        </h2>
                        <div id="collapseGatewayRaw" class="accordion-collapse collapse" aria-labelledby="headingGatewayRaw"
                             data-bs-parent="#gatewayRawAccordion">
                            <div class="accordion-body">
                                @if(!empty($gatewayRaw))
                                    <pre class="bg-light rounded p-4 mb-0" style="max-height: 320px; overflow:auto;">{{ json_encode($gatewayRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <div class="text-muted">—</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- META --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('payments.meta_title') }}</h3>
            </div>

            <div class="card-body pt-0">
                @if(!empty($meta))
                    <pre class="bg-light rounded p-4 mb-0" style="max-height: 320px; overflow:auto;">{{ json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <div class="text-muted">—</div>
                @endif
            </div>
        </div>

    </div>

    {{-- RIGHT --}}
    <div class="col-xl-4">

        {{-- SUMMARY --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('payments.summary_title') }}</h3>
            </div>

            <div class="card-body pt-0">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('payments.fields.status') }}</div>
                    <div>
                        <span class="badge badge-light-{{ $statusCls }}">{{ __('payments.status.' . $payment->status) }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('payments.fields.method') }}</div>
                    <div>
                        <span class="badge badge-light-{{ $methodCls }}">{{ __('payments.method.' . $payment->method) }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">{{ __('payments.fields.currency') }}</div>
                    <div class="fw-bold">{{ $currency }}</div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-gray-800 fw-bold fs-5">{{ __('payments.fields.amount') }}</div>
                    <div class="fw-bolder fs-2">{{ number_format((float)$payment->amount, 2) }} {{ $currency }}</div>
                </div>

                @if($payment->invoice_id && $payment->invoice)
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mt-6">
                        <i class="ki-duotone ki-bill fs-2 text-primary me-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="fw-semibold">
                            <div class="text-gray-800">{{ __('payments.invoice_notice_title') }}</div>
                            <div class="text-muted fs-7">
                                {{ __('payments.invoice_total') }}:
                                <span class="fw-bold">{{ number_format((float)$payment->invoice->total, 2) }} {{ $payment->invoice->currency }}</span>
                            </div>
                            <div class="text-muted fs-7 mt-1">
                                <a href="{{ route('dashboard.invoices.show', $payment->invoice_id) }}" class="fw-bold">
                                    {{ __('payments.open_invoice') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- PAYABLE QUICK --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('payments.links_title') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="mb-4">
                    <div class="text-muted fs-8">{{ __('payments.fields.payable') }}</div>
                    <div class="fw-bold">{{ __('payment_purposes.' . $payment->payable_type) }}</div>
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-8">{{ __('payments.fields.gateway') }}</div>
                    <div class="fw-semibold">{{ $payment->gateway ?: '—' }}</div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection