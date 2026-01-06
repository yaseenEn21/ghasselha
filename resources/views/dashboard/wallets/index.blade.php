@extends('base.layout.app')

@section('title', __('wallets.title'))

@section('content')

    @section('top-btns')
        <a href="{{ route('dashboard.wallets.create') }}" class="btn btn-primary">
            {{ __('wallets.manage_wallet') }}
        </a>
    @endsection

    <div class="card card-flush">
        <div class="card-header pt-6">
            <div class="card-title">
                <h3 class="fw-bold">{{ __('wallets.list') }}</h3>
            </div>
        </div>

        <div class="card-body pt-0">

            {{-- Filters --}}
            <div class="row g-3 mb-6">

                <div class="col-md-3">
                    <label class="form-label">{{ __('wallets.filters.user') }}</label>
                    <select id="filter_user_id" class="form-select js-user-filter"
                            data-users-ajax="{{ route('dashboard.users.select2') }}"
                            data-placeholder="{{ __('wallets.filters.user_placeholder') }}">
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ __('wallets.filters.type') }}</label>
                    <select id="filter_type" class="form-select" data-control="select2" data-placeholder="{{ __('wallets.filters.type_placeholder') }}">
                        <option value="">{{ __('wallets.filters.all') }}</option>
                        <option value="topup">{{ __('wallets.types.topup') }}</option>
                        <option value="refund">{{ __('wallets.types.refund') }}</option>
                        <option value="adjustment">{{ __('wallets.types.adjustment') }}</option>
                        <option value="booking_charge">{{ __('wallets.types.booking_charge') }}</option>
                        <option value="package_purchase">{{ __('wallets.types.package_purchase') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ __('wallets.filters.date_from') }}</label>
                    <input type="date" id="filter_date_from" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ __('wallets.filters.date_to') }}</label>
                    <input type="date" id="filter_date_to" class="form-control">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btn_reset_filters" class="btn btn-light w-100">
                        {{ __('wallets.filters.reset') }}
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="wallets_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th>{{ __('wallets.fields.user') }}</th>
                            <th>{{ __('wallets.fields.direction') }}</th>
                            <th>{{ __('wallets.fields.type') }}</th>
                            <th>{{ __('wallets.fields.amount') }}</th>
                            <th>{{ __('wallets.fields.balance_before') }}</th>
                            <th>{{ __('wallets.fields.balance_after') }}</th>
                            <th>{{ __('wallets.fields.description') }}</th>
                            <th>{{ __('wallets.fields.reference') }}</th>
                            <th>{{ __('wallets.created_at') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold"></tbody>
                </table>
            </div>

        </div>
    </div>

@endsection


@push('custom-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- DataTables (إذا عندك Metronic include جاهز تجاهل) --}}
    <script>
        (function () {
            const isAr = '{{ app()->getLocale() }}' === 'ar';

            function initUserFilter() {
                const $el = $('.js-user-filter');
                const url = $el.data('users-ajax');

                $el.select2({
                    width: '100%',
                    dir: isAr ? 'rtl' : 'ltr',
                    placeholder: $el.data('placeholder') || '',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term || '', page: params.page || 1, per_page: 10 };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return { results: data.results || [], pagination: { more: !!data.more } };
                        },
                        cache: true
                    }
                });
            }

            let dt;

            function initDatatable() {
                dt = $('#wallets_table').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    order: [[0, 'desc']],
                    ajax: {
                        url: '{{ route('dashboard.wallets.datatable') }}',
                        data: function (d) {
                            d.user_id = $('#filter_user_id').val();
                            d.direction = $('#filter_direction').val();
                            d.type = $('#filter_type').val();
                            d.date_from = $('#filter_date_from').val();
                            d.date_to = $('#filter_date_to').val();
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id' },
                        { data: 'user', name: 'user.name', orderable: false },
                        { data: 'direction_badge', name: 'direction', orderable: false, searchable: false },
                        { data: 'type_label', name: 'type', orderable: false },
                        { data: 'amount_formatted', name: 'amount' },
                        { data: 'balance_before_formatted', name: 'balance_before' },
                        { data: 'balance_after_formatted', name: 'balance_after' },
                        { data: 'description_localized', name: 'description', orderable: false },
                        { data: 'reference', name: 'referenceable_id', orderable: false },
                        { data: 'created_at', name: 'created_at' },
                    ]
                });

                $('#filter_user_id, #filter_direction, #filter_type, #filter_date_from, #filter_date_to')
                    .on('change', function () { dt.ajax.reload(); });

                $('#btn_reset_filters').on('click', function () {
                    $('#filter_direction').val('').trigger('change');
                    $('#filter_type').val('').trigger('change');
                    $('#filter_date_from').val('');
                    $('#filter_date_to').val('');
                    $('#filter_user_id').val(null).trigger('change');
                    dt.ajax.reload();
                });
            }

            $(document).ready(function () {
                initUserFilter();
                initDatatable();
            });
        })();
    </script>
@endpush