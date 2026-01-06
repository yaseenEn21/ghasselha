@extends('base.layout.app')

@section('title', __('points.title'))

@section('content')

    @section('top-btns')
        @can('points.create')
            <a href="{{ route('dashboard.points.create') }}" class="btn btn-primary">
                {{ __('points.manage_wallet') }}
            </a>
        @endcan
    @endsection

    <div class="card mb-5">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('points.filters.search_placeholder') }}</label>
                    <input type="text" id="search_custom" class="form-control"
                           placeholder="{{ __('points.filters.search_placeholder') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('points.filters.type') }}</label>
                    <select id="filter_type" class="form-select" data-control="select2"
                            data-placeholder="{{ __('points.filters.type_placeholder') }}">
                        <option value="">{{ __('points.filters.all') }}</option>
                        <option value="earn">{{ __('points.types.earn') }}</option>
                        <option value="redeem">{{ __('points.types.redeem') }}</option>
                        <option value="adjust">{{ __('points.types.adjust') }}</option>
                        <option value="refund">{{ __('points.types.refund') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('points.filters.date_from') }}</label>
                    <input type="date" id="filter_date_from" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('points.filters.date_to') }}</label>
                    <input type="date" id="filter_date_to" class="form-control">
                </div>

                
                <div class="col-md-2">
                    <button id="btn_reset_filters" class="btn btn-light mt-4 w-100">
                        {{ __('points.filters.reset') }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="points_table" class="table table-row-bordered gy-5">
                <thead>
                <tr class="fw-semibold fs-7 text-muted">
                    <th>{{ __('datatable.lbl_id') }}</th>
                    <th>{{ __('points.fields.user') }}</th>
                    <th>{{ __('points.fields.mobile') }}</th>
                    <th>{{ __('points.fields.type') }}</th>
                    <th>{{ __('points.fields.points') }}</th>
                    <th>{{ __('points.fields.money_amount') }}</th>
                    <th>{{ __('points.fields.reference') }}</th>
                    <th>{{ __('points.fields.note') }}</th>
                    <th>{{ __('datatable.lbl_created_at') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@push('custom-script')
<script>
    (function () {
        const table = $('#points_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '{{ route('dashboard.points.index') }}',
                data: function (d) {
                    d.search_custom = $('#search_custom').val();
                    d.type = $('#filter_type').val();
                    d.direction = $('#filter_direction').val();
                    d.archived = $('#filter_archived').val();
                    d.date_from = $('#filter_date_from').val();
                    d.date_to = $('#filter_date_to').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'user_name', name: 'user.name'},
                {data: 'user_mobile', name: 'user.mobile'},
                {data: 'type_label', name: 'type'},
                {data: 'points_badge', name: 'points', orderable: true, searchable: false},
                {data: 'money', name: 'money_amount', orderable: false, searchable: false},
                {data: 'reference', name: 'reference_id', orderable: false, searchable: false},
                {data: 'note', name: 'note', orderable: false, searchable: false},
                {data: 'created_at', name: 'created_at'},
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/{{ app()->getLocale() === 'ar' ? 'ar' : 'en-GB' }}.json"
            },
            rawColumns: ['points_badge'],
        });

        let debounceTimer = null;
        $('#search_custom, #filter_type, #filter_direction, #filter_archived, #filter_date_from, #filter_date_to')
            .on('keyup change', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => table.draw(), 300);
            });

        $('#btn_reset_filters').on('click', function () {
            $('#search_custom').val('');
            $('#filter_type').val('').trigger('change');
            $('#filter_direction').val('').trigger('change');
            $('#filter_archived').val('').trigger('change');
            $('#filter_date_from').val('');
            $('#filter_date_to').val('');
            table.draw();
        });
    })();
</script>
@endpush