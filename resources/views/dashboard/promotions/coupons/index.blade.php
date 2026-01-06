@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('promotion_coupons.create')
        <a href="{{ route('dashboard.promotions.coupons.create', $promotion->id) }}" class="btn btn-primary">
            {{ __('promotions.add_coupon') }}
        </a>
    @endcan
@endsection

<div class="card">
    <div class="card-body">

        <div class="row g-4 mb-6">
            <div class="col-md-6">
                <input type="text" id="search_custom" class="form-control" placeholder="{{ __('promotions.filters.search_coupon_placeholder') }}">
            </div>

            <div class="col-md-3">
                <select id="status" class="form-select">
                    <option value="">{{ __('promotions.filters.status_placeholder') }}</option>
                    <option value="active">{{ __('promotions.active') }}</option>
                    <option value="inactive">{{ __('promotions.inactive') }}</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="coupons_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>#</th>
                        <th>{{ __('promotions.coupon_code') }}</th>
                        <th>{{ __('promotions.coupon_period') }}</th>
                        <th>{{ __('promotions.usage_limit_total') }}</th>
                        <th>{{ __('promotions.usage_limit_per_user') }}</th>
                        <th>{{ __('promotions.used_count') }}</th>
                        <th>{{ __('promotions.status') }}</th>
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
    const table = $('#coupons_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.promotions.coupons.datatable', $promotion->id) }}",
            data: function (d) {
                d.search_custom = $('#search_custom').val();
                d.status = $('#status').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            {data:'id', name:'id'},
            {data:'code', name:'code'},
            {data:'period', name:'starts_at', orderable:false, searchable:false},
            {data:'usage_limit_total', name:'usage_limit_total', searchable:false},
            {data:'usage_limit_per_user', name:'usage_limit_per_user', searchable:false},
            {data:'used_count', name:'used_count', searchable:false},
            {data:'is_active_badge', name:'is_active', orderable:false, searchable:false},
            {data:'actions', name:'actions', orderable:false, searchable:false, className:'text-end'},
        ],
        drawCallback: function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#search_custom, #status').on('keyup change', function () {
        table.ajax.reload();
    });

    $(document).on('click', '.js-delete-coupon', function () {
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
                url: "{{ url('/dashboard/promotion-coupons') }}/" + id,
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