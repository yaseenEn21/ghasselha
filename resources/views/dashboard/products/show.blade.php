@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('products.edit')
        <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-light-warning">
            {{ __('products.edit') }}
        </a>
    @endcan
@endsection

@php
    $locale = app()->getLocale();
    $img = $product->getImageUrlForLocale($locale) ?: asset('assets/media/svg/files/blank-image.svg');

    $catName = '—';
    if ($product->category) {
        $n = $product->category->name ?? [];
        $catName = $n[$locale] ?? (reset($n) ?: '—');
    }
@endphp

<div class="row g-6">
    <div class="col-xl-8">

        {{-- تفاصيل المنتج --}}
        <div class="card card-flush mb-6">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('products.product_details') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="d-flex gap-6 align-items-center mb-6">
                    <div class="symbol symbol-100px">
                        <img src="{{ $img }}" class="object-fit-cover" alt="">
                    </div>

                    <div class="flex-grow-1">
                        <div class="fw-bold fs-3 mb-2">{{ $product->getLocalizedName() }}</div>

                        <div class="text-muted">
                            {{ __('products.fields.category') }}:
                            <span class="fw-semibold">{{ $catName }}</span>
                        </div>

                        <div class="text-muted">
                            {{ __('products.fields.status') }}:
                            @if($product->is_active)
                                <span class="badge badge-light-success">{{ __('products.active') }}</span>
                            @else
                                <span class="badge badge-light-danger">{{ __('products.inactive') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($product->getLocalizedDescription())
                    <div class="text-gray-700">
                        {{ $product->getLocalizedDescription() }}
                    </div>
                @else
                    <div class="text-muted">—</div>
                @endif
            </div>
        </div>

        {{-- سجل المبيعات (AJAX DataTable) --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('products.sales_lines') }}</h3>
            </div>

            <div class="card-body pt-0">

                {{-- الفلاتر --}}
                <form id="sales_filter_form" class="row g-3 mb-6" autocomplete="off">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('products.filters.from') }}</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('products.filters.to') }}</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            {{ __('products.filters.apply') }}
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="sales_lines_table" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>#</th>
                                <th>{{ __('products.booking_id') }}</th>
                                <th>{{ __('products.qty') }}</th>
                                <th>{{ __('products.unit_price') }}</th>
                                <th>{{ __('products.line_total') }}</th>
                                <th>{{ __('products.created_at') }}</th>
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
                <h3 class="card-title fw-bold">{{ __('products.pricing') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="mb-3">
                    <div class="text-muted fs-8">{{ __('products.fields.price') }}</div>
                    <div class="fw-bold fs-4">{{ number_format((float)$product->price, 2) }} SAR</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-8">{{ __('products.fields.discounted_price') }}</div>
                    <div class="fw-semibold">
                        {{ $product->discounted_price !== null ? number_format((float)$product->discounted_price, 2).' SAR' : '—' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-8">{{ __('products.fields.max_qty_per_booking') }}</div>
                    <div class="fw-semibold">{{ $product->max_qty_per_booking ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- إحصائيات المبيعات --}}
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('products.sales_stats') }}</h3>
            </div>

            <div class="card-body pt-0">
                <div class="mb-4">
                    <div class="text-muted fs-8">{{ __('products.total_sales_count') }}</div>
                    <div class="fw-bold fs-3" id="js_total_qty">{{ $totalQty }}</div>
                </div>

                <div class="mb-4">
                    <div class="text-muted fs-8">{{ __('products.total_sales_amount') }}</div>
                    <div class="fw-bold fs-3" id="js_total_sales">{{ number_format((float)$totalSales, 2) }} SAR</div>
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-8">{{ __('products.total_profit') }}</div>
                    <div class="fw-bold fs-3" id="js_total_profit">
                        @if($profit === null)
                            —
                        @else
                            {{ number_format((float)$profit, 2) }} SAR
                        @endif
                    </div>
                </div>

                <div class="text-muted fs-7 mt-4 {{ $profit === null ? '' : 'd-none' }}" id="js_profit_note">
                    {{ __('products.profit_note_missing_cost') }}
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('custom-script')
<script>
    (function () {
        const $from = $('input[name="from"]');
        const $to   = $('input[name="to"]');

        // Datatable (Server-side)
        const dt = $('#sales_lines_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: true,
            pageLength: 10,
            order: [[0, 'desc']],
            ajax: {
                url: "{{ route('dashboard.products.salesLines', $product->id) }}",
                data: function (d) {
                    d.from = $from.val();
                    d.to   = $to.val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'booking_id', name: 'booking_id' },
                { data: 'qty', name: 'qty' },
                { data: 'unit_price_snapshot', name: 'unit_price_snapshot' },
                { data: 'line_total', name: 'line_total' },
                { data: 'created_at', name: 'created_at' },
            ],
        });

        function formatMoney(v) {
            if (v === null || v === undefined) return '—';
            const n = Number(v);
            if (Number.isNaN(n)) return '—';
            return n.toFixed(2) + ' SAR';
        }

        function loadStats() {
            $.get("{{ route('dashboard.products.salesStats', $product->id) }}", {
                from: $from.val(),
                to: $to.val()
            }).done(function (res) {
                $('#js_total_qty').text(res.total_qty ?? 0);
                $('#js_total_sales').text(formatMoney(res.total_sales));

                if (res.profit === null || res.profit === undefined) {
                    $('#js_total_profit').text('—');
                    $('#js_profit_note').removeClass('d-none');
                } else {
                    $('#js_total_profit').text(formatMoney(res.profit));
                    $('#js_profit_note').addClass('d-none');
                }
            });
        }

        // فلتر: يحدّث الجدول + الإحصائيات (بدون ريفريش)
        $('#sales_filter_form').on('submit', function (e) {
            e.preventDefault();
            dt.ajax.reload();
            loadStats();
        });

        // تحميل الإحصائيات أول مرة (للتأكد أنها متطابقة مع الفلتر)
        loadStats();
    })();
</script>
@endpush