@extends('base.layout.app')

@section('title', __('package_subscriptions.title'))

@push('custom-style')
@endpush

@section('content')

    @section('top-btns')
        {{-- ممكن مستقبلاً زر إنشاء يدوي لاشتراك --}}
        {{-- @can('package_subscriptions.create')
            <a href="{{ route('dashboard.package-subscriptions.create') }}" class="btn btn-primary">
                {{ __('package_subscriptions.create_new') }}
            </a>
        @endcan --}}
    @endsection

    <div class="card mb-5">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                {{-- بحث عام --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        {{ __('package_subscriptions.filters.search_placeholder') }}
                    </label>
                    <input type="text" id="search_custom" class="form-control"
                           placeholder="{{ __('package_subscriptions.filters.search_placeholder') }}">
                </div>

                {{-- فلتر الباقة --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        {{ __('package_subscriptions.filters.package') }}
                    </label>
                    <select id="filter_package" class="form-select" data-control="select2"
                            data-placeholder="{{ __('package_subscriptions.filters.package_placeholder') }}">
                        <option value="">{{ __('package_subscriptions.filters.all') }}</option>
                        @php $locale = app()->getLocale(); @endphp
                        @foreach($packages as $package)
                            @php
                                $pName = $package->name[$locale] ?? (is_array($package->name) ? (reset($package->name) ?: '') : $package->name);
                            @endphp
                            <option value="{{ $package->id }}">{{ $pName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- فلتر الحالة --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        {{ __('package_subscriptions.filters.status') }}
                    </label>
                    <select id="filter_status" class="form-select" data-control="select2"
                            data-placeholder="{{ __('package_subscriptions.filters.status_placeholder') }}">
                        <option value="">{{ __('package_subscriptions.filters.all') }}</option>
                        <option value="active">{{ __('package_subscriptions.status_active') }}</option>
                        <option value="expired">{{ __('package_subscriptions.status_expired') }}</option>
                        <option value="cancelled">{{ __('package_subscriptions.status_cancelled') }}</option>
                    </select>
                </div>

                {{-- تواريخ البداية والنهاية (اختياري) --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        {{ __('package_subscriptions.filters.starts_from') }}
                    </label>
                    <input type="date" id="filter_starts_from" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        {{ __('package_subscriptions.filters.ends_to') }}
                    </label>
                    <input type="date" id="filter_ends_to" class="form-control">
                </div>

                <div class="col-md-2">
                    <button id="btn_reset_filters" class="btn btn-light mt-4 w-100">
                        {{ __('package_subscriptions.filters.reset') }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="package_subscriptions_table" class="table table-row-bordered gy-5">
                <thead>
                <tr class="fw-semibold fs-7 text-muted">
                    <th>{{ __('datatable.lbl_id') }}</th>
                    <th>{{ __('package_subscriptions.customer') }}</th>
                    <th>{{ __('package_subscriptions.mobile') }}</th>
                    <th>{{ __('package_subscriptions.package') }}</th>
                    <th>{{ __('package_subscriptions.status') }}</th>
                    <th>{{ __('package_subscriptions.period') }}</th>
                    <th>{{ __('package_subscriptions.remaining_washes') }}</th>
                    <th>{{ __('package_subscriptions.final_price') }}</th>
                    <th>{{ __('package_subscriptions.purchased_at') }}</th>
                    <th>{{ __('datatable.lbl_actions') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('custom-script')
    <script>
        (function () {
            const table = $('#package_subscriptions_table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('dashboard.package-subscriptions.index') }}',
                    data: function (d) {
                        d.search_custom = $('#search_custom').val();
                        d.package_id    = $('#filter_package').val();
                        d.status        = $('#filter_status').val();
                        d.starts_from   = $('#filter_starts_from').val();
                        d.ends_to       = $('#filter_ends_to').val();
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'customer_name', name: 'user.name'},
                    {data: 'customer_mobile', name: 'user.mobile'},
                    {data: 'package_name', name: 'package.name'},
                    {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                    {data: 'period', name: 'starts_at'},
                    {data: 'remaining_washes', name: 'remaining_washes'},
                    {data: 'final_price_snapshot', name: 'final_price_snapshot'},
                    {data: 'purchased_at', name: 'purchased_at'},
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/{{ app()->getLocale() === 'ar' ? 'ar' : 'en-GB' }}.json"
                }
            });

            let debounceTimer = null;
            $('#search_custom, #filter_package, #filter_status, #filter_starts_from, #filter_ends_to').on('keyup change', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => table.draw(), 300);
            });

            $('#btn_reset_filters').on('click', function () {
                $('#search_custom').val('');
                $('#filter_package').val('').trigger('change');
                $('#filter_status').val('').trigger('change');
                $('#filter_starts_from').val('');
                $('#filter_ends_to').val('');
                table.draw();
            });
        })();
    </script>
@endpush