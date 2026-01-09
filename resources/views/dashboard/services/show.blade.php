@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('services.edit')
        <a href="{{ route('dashboard.services.edit', $service->id) }}" class="btn btn-light-warning">
            {{ __('services.edit') }}
        </a>
    @endcan
@endsection

@php
    $locale = app()->getLocale();

    $img = $service->getImageUrl($locale) ?: asset('assets/media/svg/files/blank-image.svg');

    $catName = '—';
    if ($service->category) {
        $catArr = is_array($service->category->name ?? null) ? $service->category->name ?? [] : [];
        $catFirst = $catArr ? reset($catArr) : null;
        $catName = $catArr[$locale] ?? ($catFirst ?: '—');
    }

    $nameArr = is_array($service->name ?? null) ? $service->name ?? [] : [];
    $nameFirst = $nameArr ? reset($nameArr) : null;
    $name = $nameArr[$locale] ?? ($nameFirst ?: '—');

    $descArr = is_array($service->description ?? null) ? $service->description ?? [] : [];
    $descFirst = $descArr ? reset($descArr) : null;
    $desc = $descArr[$locale] ?? ($descFirst ?: null);

    $rate_count = $service->rate_count ?? 0;
    $rate = number_format((float) $service->rating_avg, 1, '.', '');
@endphp


<div class="row g-6">
    <div class="col-xl-8">

        {{-- تفاصيل الخدمة --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('services.service_details') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="d-flex gap-6 align-items-center mb-6">
                    <div class="symbol symbol-100px">
                        <img src="{{ $img }}" class="object-fit-cover" alt="">
                    </div>

                    <div class="flex-grow-1">
                        <div class="fw-bold fs-3 mb-2">{{ $name }}</div>

                        <div class="text-muted">
                            {{ __('services.category') }}:
                            <span class="fw-semibold">{{ $catName }}</span>
                        </div>

                        <div class="text-muted">
                            {{ __('services.status') }}:
                            @if ($service->is_active)
                                <span class="badge badge-light-success">{{ __('services.active') }}</span>
                            @else
                                <span class="badge badge-light-danger">{{ __('services.inactive') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($desc)
                    <div class="text-gray-700">{{ $desc }}</div>
                @else
                    <div class="text-muted">—</div>
                @endif
            </div>
        </div>

        {{-- سجل المبيعات (AJAX DataTable) --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('services.sales_lines') }}</h3>
            </div>

            <div class="card-body pt-0">

                {{-- الفلاتر --}}
                <form id="sales_filter_form" class="row g-3 mb-6" autocomplete="off">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('services.filters.from') }}</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('services.filters.to') }}</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            {{ __('services.filters.apply') }}
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="sales_lines_table" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>#</th>
                                <th>{{ __('services.booking_id') }}</th>
                                <th>{{ __('services.booking_date') }}</th>
                                <th>{{ __('services.time') }}</th>
                                <th>{{ __('services.pricing_source') }}</th>
                                <th>{{ __('services.final_price') }}</th>
                                <th>{{ __('services.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="col-xl-4">

        {{-- التسعير --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('services.pricing') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="mb-3">
                    <div class="text-muted fs-8">{{ __('services.duration') }}</div>
                    <div class="fw-semibold">
                        {{ (int) $service->duration_minutes }} {{ __('services.minutes_suffix') }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-8">{{ __('services.price') }}</div>
                    <div class="fw-bold fs-4">{{ number_format((float) $service->price, 2) }} SAR</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-8">{{ __('services.discount_price') }}</div>
                    <div class="fw-semibold">
                        {{ $service->discounted_price !== null ? number_format((float) $service->discounted_price, 2) . ' SAR' : '—' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- إحصائيات المبيعات --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('services.sales_stats') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="mb-4">
                    <div class="text-muted fs-8">{{ __('services.total_sales_count') }}</div>
                    <div class="fw-bold fs-3" id="js_total_count">{{ $totalCount }}</div>
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-8">{{ __('services.total_sales_amount') }}</div>
                    <div class="fw-bold fs-3" id="js_total_sales">{{ number_format((float) $totalSales, 2) }} SAR</div>
                </div>

                <div class="text-muted fs-7 mt-4">
                    {{ __('services.sales_note_completed_only') }}
                </div>
            </div>
        </div>

        {{-- Rating box (ضعه تحت بوكس إحصائيات المبيعات) --}}
        <div class="card card-flush mt-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('services.rating.title') }}</h3>
            </div>

            <div class="card-body pt-0">

                {{-- Average + Stars + Count --}}
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <div class="fw-bold fs-2">
                            {{ number_format((float) ($ratingAvg ?? 0), 1, '.', '') }}
                        </div>
                        <div class="text-muted fs-7">
                            {{ __('services.rating.based_on') }}
                            <span class="fw-semibold">{{ (int) ($ratingCount ?? 0) }}</span>
                            {{ __('services.rating.reviews') }}
                        </div>
                    </div>

                    @php
                        $avg = (float) ($ratingAvg ?? 0);
                        $filled = (int) round($avg); // 1..5
                        $filled = max(0, min(5, $filled));
                    @endphp

                    <div class="d-flex align-items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="ki-duotone ki-star fs-2 {{ $i <= $filled ? 'text-warning' : 'text-gray-300' }}">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        @endfor
                    </div>
                </div>

                {{-- Breakdown (5 -> 1) --}}
                @if (isset($ratingBreakdown) && is_array($ratingBreakdown) && (int) ($ratingCount ?? 0) > 0)
                    <div class="d-flex flex-column gap-3 mb-6">
                        @for ($s = 5; $s >= 1; $s--)
                            @php
                                $c = (int) ($ratingBreakdown[$s] ?? 0);
                                $total = (int) ($ratingCount ?? 0);
                                $pct = $total > 0 ? round(($c / $total) * 100) : 0;
                            @endphp

                            <div class="d-flex align-items-center gap-3">
                                <div class="text-muted fs-7" style="width:52px; white-space:nowrap;">
                                    {{ $s }} ★
                                </div>

                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                        style="width: {{ $pct }}%"></div>
                                </div>

                                <div class="text-muted fs-7" style="width:32px; text-align:end;">
                                    {{ $c }}
                                </div>
                            </div>
                        @endfor
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('custom-script')
<script>
    (function() {
        const $from = $('input[name="from"]');
        const $to = $('input[name="to"]');

        const dt = $('#sales_lines_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            order: [
                [1, 'desc']
            ], // booking_id desc
            ajax: {
                url: "{{ route('dashboard.services.salesLines', $service->id) }}",
                data: function(d) {
                    d.from = $from.val();
                    d.to = $to.val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'booking_id',
                    name: 'id'
                },
                {
                    data: 'booking_date',
                    name: 'booking_date'
                },
                {
                    data: 'time',
                    name: 'time',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'pricing_source',
                    name: 'service_pricing_source',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'final_price',
                    name: 'service_final_price_snapshot'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
            ],
        });

        function formatMoney(v) {
            const n = Number(v);
            if (Number.isNaN(n)) return '0.00 SAR';
            return n.toFixed(2) + ' SAR';
        }

        function loadStats() {
            $.get("{{ route('dashboard.services.salesStats', $service->id) }}", {
                from: $from.val(),
                to: $to.val()
            }).done(function(res) {
                $('#js_total_count').text(res.total_count ?? 0);
                $('#js_total_sales').text(formatMoney(res.total_sales ?? 0));
            });
        }

        $('#sales_filter_form').on('submit', function(e) {
            e.preventDefault();
            dt.ajax.reload();
            loadStats();
        });

        loadStats();
    })();
</script>
@endpush
