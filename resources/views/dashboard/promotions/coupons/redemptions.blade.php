{{-- redemptions --}}
@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('promotions.edit')
        <a href="{{ route('dashboard.promotions.coupons.edit', [$promotion->id, $coupon->id]) }}" class="btn btn-light-warning">
            {{ __('promotions.coupons.edit') ?? __('promotions.edit') }}
        </a>
    @endcan
@endsection

@php
    $locale = app()->getLocale();

    $promotionNameArr = $promotion->name ?? [];
    $promotionName = is_array($promotionNameArr)
        ? ($promotionNameArr[$locale] ?? (collect($promotionNameArr)->first() ?? '—'))
        : '—';

    $couponPeriod = ($coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : '—') . ' → ' . ($coupon->ends_at ? $coupon->ends_at->format('Y-m-d') : '—');
    $promotionPeriod = ($promotion->starts_at ? $promotion->starts_at->format('Y-m-d') : '—') . ' → ' . ($promotion->ends_at ? $promotion->ends_at->format('Y-m-d') : '—');

    $discountLabel = $coupon->discount_type === 'percent'
        ? (rtrim(rtrim(number_format((float)$coupon->discount_value, 2), '0'), '.') . '%')
        : (number_format((float)$coupon->discount_value, 2) . ' SAR');

    $maxDiscountLabel = $coupon->max_discount !== null ? (number_format((float)$coupon->max_discount, 2) . ' SAR') : '—';
    $minInvoiceLabel = $coupon->min_invoice_total !== null ? (number_format((float)$coupon->min_invoice_total, 2) . ' SAR') : '—';

    $appliesToLabel = __('promotions.applies_to_' . $coupon->applies_to);

    $showServices = in_array($coupon->applies_to, ['service','both'], true);
    $showPackages = in_array($coupon->applies_to, ['package','both'], true);

    // قوائم الخدمات/الباقات (لو العلاقات موجودة ومحمّلة)
    $services = (method_exists($coupon, 'services') && $coupon->relationLoaded('services')) ? $coupon->services : collect();
    $packages = (method_exists($coupon, 'packages') && $coupon->relationLoaded('packages')) ? $coupon->packages : collect();

    $appliedCount = (int)($coupon->redemptions_applied_count ?? 0);
    $voidedCount = (int)($coupon->redemptions_voided_count ?? 0);
    $discountSum = $coupon->discount_applied_sum ?? 0;
@endphp

{{-- ===== Top summary cards ===== --}}
<div class="row g-6 mb-6">
    {{-- Coupon info --}}
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('promotions.coupons.basic_data') }}</h3>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.coupons.fields.code') }}</div>
                    <div class="fw-bold fs-4">{{ $coupon->code }}</div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.coupons.fields.status') }}</div>
                    <div>
                        @if($coupon->is_active)
                            <span class="badge badge-light-success">{{ __('promotions.active') }}</span>
                        @else
                            <span class="badge badge-light-danger">{{ __('promotions.inactive') }}</span>
                        @endif
                    </div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.coupons.fields.starts_at') }}</div>
                    <div class="fw-semibold">{{ $coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : '—' }}</div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.coupons.fields.ends_at') }}</div>
                    <div class="fw-semibold">{{ $coupon->ends_at ? $coupon->ends_at->format('Y-m-d') : '—' }}</div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.discount_type') }}</div>
                    <div class="fw-semibold">
                        {{ $coupon->discount_type === 'percent' ? __('promotions.discount_type_percent') : __('promotions.discount_type_fixed') }}
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.discount_value') }}</div>
                    <div class="fw-bold">{{ $discountLabel }}</div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.max_discount') }}</div>
                    <div class="fw-semibold">{{ $maxDiscountLabel }}</div>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-muted">{{ __('promotions.coupons.fields.min_invoice_total') }}</div>
                    <div class="fw-semibold">{{ $minInvoiceLabel }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scope info --}}
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('promotions.scope') }}</h3>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.applies_to') }}</div>
                    <div class="fw-bold">{{ $appliesToLabel }}</div>
                </div>

                <div class="separator my-4"></div>

                {{-- Services scope --}}
                @if($showServices)
                    <div class="mb-5">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-semibold">{{ __('promotions.applies_to_service') }}</div>
                            @if($coupon->apply_all_services)
                                <span class="badge badge-light-info">{{ __('promotions.apply_all_services') }}</span>
                            @endif
                        </div>

                        @if($coupon->apply_all_services)
                            <div class="text-muted fs-7">{{ __('promotions.apply_all_services') }}</div>
                        @else
                            @if($services->count() > 0)
                                <ul class="mb-0 ps-5">
                                    @foreach($services->take(6) as $s)
                                        @php
                                            $arr = $s->name ?? [];
                                            $txt = is_array($arr) ? ($arr[$locale] ?? (collect($arr)->first() ?? '—')) : '—';
                                        @endphp
                                        <li class="text-gray-700">{{ $txt }}</li>
                                    @endforeach
                                </ul>

                                @if($services->count() > 6)
                                    <div class="text-muted fs-7 mt-2">
                                        {{ __('promotions.more') ?? 'وغيرها' }} ({{ $services->count() - 6 }})
                                    </div>
                                @endif
                            @else
                                <div class="text-muted">—</div>
                            @endif
                        @endif
                    </div>
                @endif

                {{-- Packages scope --}}
                @if($showPackages)
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-semibold">{{ __('promotions.applies_to_package') }}</div>
                            @if($coupon->apply_all_packages)
                                <span class="badge badge-light-info">{{ __('promotions.apply_all_packages') }}</span>
                            @endif
                        </div>

                        @if($coupon->apply_all_packages)
                            <div class="text-muted fs-7">{{ __('promotions.apply_all_packages') }}</div>
                        @else
                            @if($packages->count() > 0)
                                <ul class="mb-0 ps-5">
                                    @foreach($packages->take(6) as $p)
                                        @php
                                            $arr = $p->name ?? [];
                                            $txt = is_array($arr) ? ($arr[$locale] ?? (collect($arr)->first() ?? '—')) : '—';
                                        @endphp
                                        <li class="text-gray-700">{{ $txt }}</li>
                                    @endforeach
                                </ul>

                                @if($packages->count() > 6)
                                    <div class="text-muted fs-7 mt-2">
                                        {{ __('promotions.more') ?? 'وغيرها' }} ({{ $packages->count() - 6 }})
                                    </div>
                                @endif
                            @else
                                <div class="text-muted">—</div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Usage stats + promotion info --}}
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('promotions.coupons.redemptions_title') }}</h3>
            </div>
            <div class="card-body pt-0">

                <div class="mb-5">
                    <div class="text-muted fs-8">{{ __('promotions.coupons.fields.usage_limit_total') }}</div>
                    <div class="fw-semibold">
                        {{ $coupon->used_count ?? 0 }}
                        <span class="text-muted">/</span>
                        {{ $coupon->usage_limit_total ?? '—' }}
                    </div>

                    <div class="text-muted fs-8 mt-3">{{ __('promotions.coupons.fields.usage_limit_per_user') }}</div>
                    <div class="fw-semibold">{{ $coupon->usage_limit_per_user ?? '—' }}</div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.coupons.status_applied') }}</div>
                    <div class="fw-bold">{{ $appliedCount }}</div>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <div class="text-muted">{{ __('promotions.coupons.status_voided') }}</div>
                    <div class="fw-bold">{{ $voidedCount }}</div>
                </div>

                <div class="d-flex justify-content-between">
                    <div class="text-muted">{{ __('promotions.coupons.discount_amount') }}</div>
                    <div class="fw-bold">{{ number_format((float)$discountSum, 2) }} SAR</div>
                </div>

                <div class="separator my-4"></div>

                <div class="text-muted fs-8 mb-1">{{ __('promotions.title') }}</div>
                <div class="fw-bold">{{ $promotionName }}</div>
                <div class="text-muted fs-7 mt-2">{{ $promotionPeriod }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Redemptions table ===== --}}
<div class="card">
    <div class="card-body">

        <div class="row g-4 mb-6">
            <div class="col-md-4">
                <input type="text" id="search_custom" class="form-control"
                       placeholder="{{ __('promotions.coupons.redemptions_search_placeholder') }}">
            </div>

            <div class="col-md-3">
                <select id="status" class="form-select">
                    <option value="">{{ __('promotions.coupons.filters.status_placeholder') }}</option>
                    <option value="applied">{{ __('promotions.coupons.status_applied') }}</option>
                    <option value="voided">{{ __('promotions.coupons.status_voided') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" id="from" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="date" id="to" class="form-control">
            </div>

            <div class="col-md-1">
                <button type="button" id="reset_filters" class="btn btn-light w-100">
                    <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="redemptions_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>#</th>
                    <th>{{ __('promotions.coupons.user') }}</th>
                    <th>{{ __('promotions.coupons.invoice') }}</th>
                    <th>{{ __('promotions.coupons.discount_amount') }}</th>
                    <th>{{ __('promotions.coupons.status') }}</th>
                    <th>{{ __('promotions.coupons.applied_at') }}</th>
                    <th>{{ __('promotions.created_at') }}</th>
                </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection

@push('custom-script')
<script>
(function () {
    const table = $('#redemptions_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.promotions.coupons.redemptions.datatable', [$promotion->id, $coupon->id]) }}",
            data: function (d) {
                d.search_custom = $('#search_custom').val();
                d.status = $('#status').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'id', name: 'id'},
            {data: 'user_label', name: 'user_id', orderable: false, searchable: false},
            {data: 'invoice_label', name: 'invoice_id', orderable: false, searchable: false},
            {data: 'discount_amount', name: 'discount_amount', searchable: false},
            {data: 'status_badge', name: 'status', orderable: false, searchable: false},
            {data: 'applied_at', name: 'applied_at', orderable: false, searchable: false},
            {data: 'created_at', name: 'created_at', orderable: false, searchable: false},
        ],
        drawCallback: function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#search_custom, #status, #from, #to').on('keyup change', function () {
        table.ajax.reload();
    });

    $('#reset_filters').on('click', function () {
        $('#search_custom').val('');
        $('#status').val('');
        $('#from').val('');
        $('#to').val('');
        table.ajax.reload();
    });
})();
</script>
@endpush