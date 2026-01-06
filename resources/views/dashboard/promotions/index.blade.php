@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('promotions.create')
        <a href="{{ route('dashboard.promotions.create') }}" class="btn btn-primary">
            {{ __('promotions.create_new') }}
        </a>
    @endcan
@endsection

<div class="card">
    <div class="card-body">
        <div class="row g-4 mb-6">
            <div class="col-md-4">
                <input type="text" id="search_custom" class="form-control" placeholder="{{ __('promotions.filters.search_placeholder') }}">
            </div>

            <div class="col-md-2">
                <select id="status" class="form-select">
                    <option value="">{{ __('promotions.filters.status_placeholder') }}</option>
                    <option value="active">{{ __('promotions.active') }}</option>
                    <option value="inactive">{{ __('promotions.inactive') }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="applies_to" class="form-select">
                    <option value="">{{ __('promotions.filters.applies_to_placeholder') }}</option>
                    <option value="service">{{ __('promotions.applies_to_service') }}</option>
                    <option value="package">{{ __('promotions.applies_to_package') }}</option>
                    <option value="both">{{ __('promotions.applies_to_both') }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="discount_type" class="form-select">
                    <option value="">{{ __('promotions.filters.discount_type_placeholder') }}</option>
                    <option value="percent">{{ __('promotions.discount_type_percent') }}</option>
                    <option value="fixed">{{ __('promotions.discount_type_fixed') }}</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="promotions_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>#</th>
                        <th>{{ __('promotions.fields.name') }}</th>
                        <th>{{ __('promotions.fields.period') }}</th>
                        <th>{{ __('promotions.fields.coupons_count') }}</th>
                        <th>{{ __('promotions.fields.status') }}</th>
                        <th class="text-end">{{ __('promotions.actions_title') }}</th>
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
    const table = $('#promotions_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.promotions.datatable') }}",
            data: function (d) {
                d.search_custom = $('#search_custom').val();
                d.status = $('#status').val();
                d.applies_to = $('#applies_to').val();
                d.discount_type = $('#discount_type').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name_localized', name: 'name'},
            {data: 'period', name: 'starts_at', orderable: false, searchable: false},
            {data: 'coupons_count', name: 'coupons_count', searchable: false},
            {data: 'is_active_badge', name: 'is_active', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false, className:'text-end'},
        ],
        drawCallback: function () {
            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#search_custom, #status, #applies_to, #discount_type').on('keyup change', function () {
        table.ajax.reload();
    });

    $(document).on('click', '.js-delete-promotion', function () {
        const id = $(this).data('id');

        Swal.fire({
            icon: 'warning',
            title: "{{ __('promotions.delete_confirm_title') }}",
            text: "{{ __('promotions.delete_confirm_text') }}",
            showCancelButton: true,
            confirmButtonText: "{{ __('promotions.delete') }}",
            cancelButtonText: "{{ __('promotions.cancel') }}"
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('/dashboard/promotions') }}/" + id,
                method: 'POST',
                data: {_method:'DELETE', _token:"{{ csrf_token() }}"},
                success: function (res) {
                    Swal.fire({icon:'success', title:"{{ __('promotions.done') }}", text: res.message});
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