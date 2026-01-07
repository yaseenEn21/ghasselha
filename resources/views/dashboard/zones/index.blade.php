@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('zones.create')
        <a href="{{ route('dashboard.zones.create') }}" class="btn btn-primary">
            {{ __('zones.create_new') }}
        </a>
    @endcan
@endsection

<div class="card">
    <div class="card-body">

        <div class="row g-4 mb-6">
            <div class="col-md-4">
                <input type="text" id="search_custom" class="form-control"
                       placeholder="{{ __('zones.filters.search_placeholder') }}">
            </div>

            <div class="col-md-3">
                <select id="status" class="form-select">
                    <option value="">{{ __('zones.filters.status_placeholder') }}</option>
                    <option value="active">{{ __('zones.active') }}</option>
                    <option value="inactive">{{ __('zones.inactive') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" id="from" class="form-control">
            </div>

            <div class="col-md-2">
                <input type="date" id="to" class="form-control">
            </div>

            <div class="col-md-1">
                <button type="button" id="reset_filters" class="btn btn-light w-100"
                        data-bs-toggle="tooltip" title="{{ __('zones.filters.reset') }}">
                    <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="zones_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>#</th>
                    <th>{{ __('zones.fields.name') }}</th>
                    {{-- <th>{{ __('zones.fields.polygon') }}</th> --}}
                    {{-- <th>{{ __('zones.fields.bbox') }}</th> --}}
                    {{-- <th>{{ __('zones.fields.center') }}</th> --}}
                    {{-- <th>{{ __('zones.fields.sort_order') }}</th> --}}
                    <th>{{ __('zones.fields.prices_count') }}</th>
                    <th>{{ __('zones.fields.status') }}</th>
                    <th>{{ __('zones.fields.created_at') }}</th>
                    <th class="text-end">{{ __('zones.actions_title') }}</th>
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
    const table = $('#zones_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.zones.datatable') }}",
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
            {data: 'name', name: 'name'},
            // {data: 'polygon_badge', name: 'polygon', orderable:false, searchable:false},
            // {data: 'bbox_label', name: 'min_lat', orderable:false, searchable:false},
            // {data: 'center_label', name: 'center_lat', orderable:false, searchable:false},
            // {data: 'sort_order', name: 'sort_order', searchable:false},
            {data: 'prices_count', name: 'service_zone_prices_count', searchable:false},
            {data: 'is_active_badge', name: 'is_active', orderable:false, searchable:false},
            {data: 'created_at_label', name: 'created_at', orderable:false, searchable:false},
            {data: 'actions', name: 'actions', orderable:false, searchable:false, className:'text-end'},
        ],
        drawCallback: function () {
            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
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

    $(document).on('click', '.js-delete-zone', function () {
        const id = $(this).data('id');

        Swal.fire({
            icon: 'warning',
            title: "{{ __('zones.delete_confirm_title') }}",
            text: "{{ __('zones.delete_confirm_text') }}",
            showCancelButton: true,
            confirmButtonText: "{{ __('zones.delete') }}",
            cancelButtonText: "{{ __('zones.cancel') }}"
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('/dashboard/zones') }}/" + id,
                method: 'POST',
                data: {_method:'DELETE', _token:"{{ csrf_token() }}"},
                success: function (res) {
                    Swal.fire({icon:'success', title:"{{ __('zones.done') }}", text: res.message});
                    table.ajax.reload();
                },
                error: function () {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });
})();
</script>
@endpush