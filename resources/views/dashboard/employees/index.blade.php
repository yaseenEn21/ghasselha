@extends('base.layout.app')

@section('title', __('employees.title'))

@push('custom-style')
@endpush

@section('content')

    @section('top-btns')
        @can('employees.create')
            <a href="{{ route('dashboard.employees.create') }}" class="btn btn-primary">
                {{ __('employees.create_new') }}
            </a>
        @endcan
    @endsection

    <div class="card mb-5">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        {{ __('employees.filters.search_placeholder') }}
                    </label>
                    <input type="text" id="search_custom" class="form-control"
                           placeholder="{{ __('employees.filters.search_placeholder') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        {{ __('employees.filters.status') }}
                    </label>
                    <select id="filter_status" class="form-select" data-control="select2"
                            data-placeholder="{{ __('employees.filters.status_placeholder') }}">
                        <option value="">{{ __('employees.filters.all') }}</option>
                        <option value="active">{{ __('employees.status_active') }}</option>
                        <option value="inactive">{{ __('employees.status_inactive') }}</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        {{ __('employees.filters.gender') }}
                    </label>
                    <select id="filter_gender" class="form-select" data-control="select2"
                            data-placeholder="{{ __('employees.filters.gender_placeholder') }}">
                        <option value="">{{ __('employees.filters.all') }}</option>
                        <option value="male">{{ __('employees.fields.gender_male') }}</option>
                        <option value="female">{{ __('employees.fields.gender_female') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button id="btn_reset_filters" class="btn btn-light mt-4 w-100">
                        {{ __('employees.filters.reset') }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="employees_table" class="table table-row-bordered gy-5">
                <thead>
                <tr class="fw-semibold fs-7 text-muted">
                    <th>{{ __('datatable.lbl_id') }}</th>
                    <th>{{ __('employees.fields.name') }}</th>
                    <th>{{ __('employees.fields.mobile') }}</th>
                    <th>{{ __('employees.fields.email') }}</th>
                    <th>{{ __('employees.fields.gender') }}</th>
                    <th>{{ __('employees.status') }}</th>
                    <th>{{ __('datatable.lbl_created_at') }}</th>
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
            const table = $('#employees_table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('dashboard.employees.index') }}',
                    data: function (d) {
                        d.search_custom = $('#search_custom').val();
                        d.status        = $('#filter_status').val();
                        d.gender        = $('#filter_gender').val();
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'user.name'},
                    {data: 'mobile', name: 'user.mobile'},
                    {data: 'email', name: 'user.email'},
                    {data: 'gender', name: 'user.gender'},
                    {data: 'status_badge', name: 'is_active', orderable: false, searchable: false},
                    {data: 'created_at', name: 'created_at'},
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
            $('#search_custom, #filter_status, #filter_gender').on('keyup change', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => table.draw(), 300);
            });

            $('#btn_reset_filters').on('click', function () {
                $('#search_custom').val('');
                $('#filter_status').val('').trigger('change');
                $('#filter_gender').val('').trigger('change');
                table.draw();
            });
        })();
    </script>
@endpush