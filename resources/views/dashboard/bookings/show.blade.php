@extends('base.layout.app')

@section('title', __('bookings.view'))

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.bookings.index') }}" class="btn btn-light">
        {{ __('bookings.back_to_list') }}
    </a>

    {{-- لاحقاً: أزرار تعديل/إلغاء/تغيير حالة --}}
@endsection

@php
    $locale = app()->getLocale();

    $serviceName = $booking->service?->name
        ? (is_array($booking->service->name)
            ? $booking->service->name[$locale] ?? collect($booking->service->name)->first()
            : $booking->service->name)
        : '—';

    // car/address fields (عدّلها حسب أعمدتك)
    $carLabel =
        data_get($booking, 'car.plate_number') ??
        (data_get($booking, 'car.plate') ?? (data_get($booking, 'car.number') ?? '#' . (int) $booking->car_id));

    $addressLabel =
        data_get($booking, 'address.title') ??
        (data_get($booking, 'address.address') ??
            (data_get($booking, 'address.full_address') ?? '#' . (int) $booking->address_id));

    $tpLabel = $booking->time_period ? __('bookings.time_period.' . $booking->time_period) : '—';

    $statusKey = $booking->status ?: 'pending';

    $statusClass = match ($statusKey) {
        'pending' => 'warning',
        'confirmed' => 'primary',
        'moving' => 'info',
        'arrived' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary',
    };

    $timeRange =
        ($booking->start_time ? substr((string) $booking->start_time, 0, 5) : '—') .
        ' → ' .
        ($booking->end_time ? substr((string) $booking->end_time, 0, 5) : '—');

    // Rating
    $ratingValue = $booking->rating !== null ? (int) $booking->rating : null;
    $ratingComment = $booking->rating_comment ?? null;
@endphp

{{-- Header Summary --}}
<div class="card mb-6">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">

            <div class="d-flex align-items-center gap-4">
                <div class="symbol symbol-50px symbol-circle">
                    <span class="symbol-label bg-light-{{ $statusClass }} text-{{ $statusClass }} fw-bold">
                        #{{ $booking->id }}
                    </span>
                </div>

                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fs-3 fw-bold">{{ __('bookings.booking') }} #{{ $booking->id }}</span>
                        <span
                            class="badge badge-light-{{ $statusClass }}">{{ __('bookings.status.' . $statusKey) }}</span>
                    </div>
                    <div class="text-muted fw-semibold">
                        {{ $booking->booking_date?->format('Y-m-d') }} • {{ $timeRange }} • {{ $tpLabel }}
                        • {{ __('bookings.duration') }}: {{ (int) $booking->duration_minutes }}
                        {{ __('bookings.minutes') }}
                    </div>
                    {{-- Rating (if exists) --}}
                    @if ($ratingValue !== null)
                        <div class="d-flex flex-column gap-1 mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="ki-duotone ki-star fs-5 {{ $i <= $ratingValue ? 'text-warning' : 'text-gray-300' }}">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    @endfor
                                </div>

                                <span class="text-muted fs-7">({{ $ratingValue }}/5)</span>

                                @if ($booking->rated_at)
                                    <span class="text-muted fs-7">•
                                        {{ $booking->rated_at?->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>

                            @if (!empty($ratingComment))
                                <div class="text-gray-700 fs-7">
                                    {{ $ratingComment }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>

            <div class="d-flex flex-wrap gap-3">
                <div class="d-flex flex-column text-end">
                    <span class="text-muted fs-7">{{ __('bookings.total_snapshot') }}</span>
                    <span class="fs-2 fw-bold">
                        {{ number_format((float) $booking->total_snapshot, 2) }} {{ $booking->currency }}
                    </span>
                </div>

                <div class="separator separator-dashed my-2 d-none d-md-block"></div>

                <div class="d-flex flex-column text-end">
                    <span class="text-muted fs-7">{{ __('bookings.service') }}</span>
                    <span class="fs-6 fw-bold">{{ $serviceName }}</span>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="card">
    <div class="card-body">

        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-6 fs-6">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab_overview">
                    {{ __('bookings.tabs.overview') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab_products">
                    {{ __('bookings.tabs.products') }}
                    <span class="badge badge-light ms-2">{{ $booking->products->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab_invoices">
                    {{ __('bookings.tabs.invoices') }}
                    <span class="badge badge-light ms-2">{{ $booking->invoices->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab_logs">
                    {{ __('bookings.tabs.logs') }}
                    <span class="badge badge-light ms-2">{{ $booking->statusLogs->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab_meta">
                    {{ __('bookings.tabs.meta') }}
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- Overview --}}
            <div class="tab-pane fade show active" id="tab_overview" role="tabpanel">

                <div class="row g-6">

                    {{-- Customer --}}
                    <div class="col-lg-4">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('bookings.customer.title') }}</h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="symbol symbol-45px symbol-circle">
                                        <span class="symbol-label bg-light-primary text-primary fw-bold">
                                            {{ mb_substr($booking->user?->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-6">{{ $booking->user?->name ?? '—' }}</span>
                                        <span class="text-muted">{{ $booking->user?->mobile ?? '—' }}</span>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-4"></div>

                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.user_id') }}</span>
                                        <span class="fw-semibold">{{ $booking->user_id }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.car.title') }}</span>
                                        <span class="fw-semibold">{{ $carLabel }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.address.title') }}</span>
                                        <span class="fw-semibold">{{ $addressLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Assignment --}}
                    <div class="col-lg-4">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('bookings.assignment') }}</h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.employee') }}</span>
                                        <span class="fw-semibold">
                                            {{ $booking->employee_id ? '#' . (int) $booking->employee_id : '—' }}
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.zone') }}</span>
                                        <span
                                            class="fw-semibold">{{ $booking->zone_id ? '#' . (int) $booking->zone_id : '—' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.time_period_label') }}</span>
                                        <span class="fw-semibold">{{ $tpLabel }}</span>
                                    </div>

                                    <div class="separator separator-dashed my-2"></div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.package_subscription') }}</span>
                                        <span class="fw-semibold">
                                            {{ $booking->package_subscription_id ? '#' . (int) $booking->package_subscription_id : '—' }}
                                        </span>
                                    </div>
                                </div>

                                @if ($booking->package_subscription_id)
                                    <div
                                        class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mt-6">
                                        <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                                            <span class="path1"></span><span class="path2"></span><span
                                                class="path3"></span>
                                        </i>
                                        <div class="d-flex flex-stack flex-grow-1">
                                            <div class="fw-semibold">
                                                <div class="fs-6 text-gray-700">
                                                    {{ __('bookings.package_cover_hint') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Pricing --}}
                    <div class="col-lg-4">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('bookings.pricing') }}</h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">

                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.pricing_source') }}</span>
                                        <span
                                            class="fw-semibold">{{ __('bookings.pricing_source_values.' . ($booking->service_pricing_source ?? 'base')) }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.service_unit_price') }}</span>
                                        <span
                                            class="fw-semibold">{{ number_format((float) $booking->service_unit_price_snapshot, 2) }}
                                            {{ $booking->currency }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.service_charge_amount') }}</span>
                                        <span
                                            class="fw-semibold">{{ number_format((float) $booking->service_charge_amount_snapshot, 2) }}
                                            {{ $booking->currency }}</span>
                                    </div>

                                    <div class="separator separator-dashed my-2"></div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.subtotal') }}</span>
                                        <span
                                            class="fw-semibold">{{ number_format((float) $booking->subtotal_snapshot, 2) }}
                                            {{ $booking->currency }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.discount') }}</span>
                                        <span
                                            class="fw-semibold">{{ number_format((float) $booking->discount_snapshot, 2) }}
                                            {{ $booking->currency }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ __('bookings.tax') }}</span>
                                        <span
                                            class="fw-semibold">{{ number_format((float) $booking->tax_snapshot, 2) }}
                                            {{ $booking->currency }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fw-bold">{{ __('bookings.total') }}</span>
                                        <span class="fw-bold">{{ number_format((float) $booking->total_snapshot, 2) }}
                                            {{ $booking->currency }}</span>
                                    </div>
                                </div>

                                @if (!empty($booking->service_pricing_meta))
                                    <div class="mt-6">
                                        <div class="text-muted fw-semibold mb-2">{{ __('bookings.pricing_meta') }}
                                        </div>
                                        <pre class="bg-light p-4 rounded">{{ json_encode($booking->service_pricing_meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                </div>

                {{-- Status timestamps --}}
                <div class="row g-6 mt-1">
                    <div class="col-lg-12">
                        <div class="card card-flush">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('bookings.lifecycle') }}</h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row g-6">
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <span class="text-muted">{{ __('bookings.created_at') }}</span>
                                            <span
                                                class="fw-semibold">{{ $booking->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <span class="text-muted">{{ __('bookings.confirmed_at') }}</span>
                                            <span
                                                class="fw-semibold">{{ $booking->confirmed_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <span class="text-muted">{{ __('bookings.cancelled_at') }}</span>
                                            <span
                                                class="fw-semibold">{{ $booking->cancelled_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if ($booking->status === 'cancelled')
                                    <div
                                        class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-4 mt-6">
                                        <i class="ki-duotone ki-information-5 fs-2tx text-danger me-4">
                                            <span class="path1"></span><span class="path2"></span><span
                                                class="path3"></span>
                                        </i>
                                        <div class="d-flex flex-stack flex-grow-1">
                                            <div class="fw-semibold">
                                                <div class="fs-6 text-gray-800">
                                                    <div class="fw-bold mb-1">{{ __('bookings.cancel_reason') }}:
                                                        {{ $booking->cancel_reason ?? '—' }}</div>
                                                    <div class="text-muted">{{ $booking->cancel_note ?? '—' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Products --}}
            <div class="tab-pane fade" id="tab_products" role="tabpanel">

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>#</th>
                                <th>{{ __('bookings.products.product') }}</th>
                                <th>{{ __('bookings.products.qty') }}</th>
                                <th>{{ __('bookings.products.unit_price') }}</th>
                                <th>{{ __('bookings.products.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->products as $i => $p)
                                @php
                                    $title = $p->title
                                        ? (is_array($p->title)
                                            ? $p->title[$locale] ?? collect($p->title)->first()
                                            : $p->title)
                                        : null;
                                    $fallbackTitle = $p->product?->name ?? '#' . $p->product_id;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $title ?: $fallbackTitle }}</div>
                                        <div class="text-muted fs-7">{{ __('bookings.products.product_id') }}:
                                            #{{ $p->product_id }}</div>
                                    </td>
                                    <td>{{ (int) $p->qty }}</td>
                                    <td>{{ number_format((float) $p->unit_price_snapshot, 2) }}
                                        {{ $booking->currency }}</td>
                                    <td class="fw-bold">{{ number_format((float) $p->line_total, 2) }}
                                        {{ $booking->currency }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-10">
                                        {{ __('bookings.products.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-6">
                    <div class="d-flex flex-column text-end">
                        <span class="text-muted">{{ __('bookings.products_subtotal') }}</span>
                        <span
                            class="fs-4 fw-bold">{{ number_format((float) $booking->products_subtotal_snapshot, 2) }}
                            {{ $booking->currency }}</span>
                    </div>
                </div>

            </div>

            {{-- Invoices --}}
            <div class="tab-pane fade" id="tab_invoices" role="tabpanel">

                @if ($latestUnpaid)
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-6">
                        <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <div class="fs-6 text-gray-800">
                                    {{ __('bookings.latest_unpaid_invoice_hint') }}
                                    <span class="fw-bold ms-2">#{{ $latestUnpaid->id }}
                                        ({{ $latestUnpaid->number ?? '—' }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>#</th>
                                <th>{{ __('bookings.invoices.number') }}</th>
                                <th>{{ __('bookings.invoices.status') }}</th>
                                <th>{{ __('bookings.invoices.type') }}</th>
                                <th>{{ __('bookings.invoices.total') }}</th>
                                <th>{{ __('bookings.invoices.paid_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->invoices as $inv)
                                @php
                                    $invStatusClass = match ($inv->status) {
                                        'paid' => 'success',
                                        'unpaid' => 'warning',
                                        'cancelled' => 'danger',
                                        'refunded' => 'info',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>#{{ $inv->id }}</td>
                                    <td class="fw-bold">{{ $inv->number ?? '—' }}</td>
                                    <td><span
                                            class="badge badge-light-{{ $invStatusClass }}">{{ __('invoice_statuses.' . $inv->status) }}</span>
                                    </td>
                                    <td>{{ $inv->type }}</td>
                                    <td class="fw-bold">{{ number_format((float) $inv->total, 2) }}
                                        {{ $inv->currency }}</td>
                                    <td>{{ $inv->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-10">
                                        {{ __('bookings.invoices.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Logs --}}
            <div class="tab-pane fade" id="tab_logs" role="tabpanel">

                <div class="timeline-label">
                    @forelse($booking->statusLogs as $log)
                        <div class="timeline-item">
                            <div class="timeline-label fw-bold text-gray-800 fs-6">
                                {{ $log->created_at?->format('Y-m-d H:i') ?? '—' }}
                            </div>

                            <div class="timeline-badge">
                                <i class="fa fa-genderless text-primary fs-1"></i>
                            </div>

                            <div class="timeline-content ps-3">
                                <div class="fw-semibold">
                                    {{ __('bookings.logs.from') }}:
                                    <span
                                        class="badge badge-light">{{ $log->from_status ? __('bookings.status.' . $log->from_status) : '—' }}</span>

                                    <span class="mx-2">→</span>

                                    {{ __('bookings.logs.to') }}:
                                    <span
                                        class="badge badge-light-primary">{{ __('bookings.status.' . $log->to_status) }}</span>
                                </div>

                                @if ($log->note)
                                    <div class="text-muted mt-2">{{ $log->note }}</div>
                                @endif

                                @if ($log->created_by)
                                    <div class="text-muted fs-7 mt-1">{{ __('bookings.logs.by') }}:
                                        #{{ (int) $log->created_by }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-10">
                            {{ __('bookings.logs.empty') }}
                        </div>
                    @endforelse
                </div>

            </div>

            {{-- Meta --}}
            <div class="tab-pane fade" id="tab_meta" role="tabpanel">
                <div class="row g-6">
                    <div class="col-lg-6">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('bookings.meta') }}</h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                @if (!empty($booking->meta))
                                    <pre class="bg-light p-4 rounded">{{ json_encode($booking->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <div class="text-muted">{{ __('bookings.meta_empty') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('bookings.raw') }}</h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <pre class="bg-light p-4 rounded">{{ json_encode($booking->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
