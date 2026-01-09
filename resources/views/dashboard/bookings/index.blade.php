@extends('base.layout.app')

@section('title', __('bookings.title'))

@section('content')

@section('top-btns')
    {{-- لاحقاً: زر create --}}
@endsection

<div class="card">
    <div class="card-body">

        {{-- Filters --}}
        <div class="row g-4 mb-6">
            <div class="col-lg-4">
                <input type="text" id="search_custom" class="form-control"
                       placeholder="{{ __('bookings.filters.search_placeholder') }}">
            </div>

            <div class="col-lg-2">
                <select id="status" class="form-select">
                    <option value="">{{ __('bookings.filters.status_placeholder') }}</option>
                    <option value="pending">{{ __('bookings.status.pending') }}</option>
                    <option value="confirmed">{{ __('bookings.status.confirmed') }}</option>
                    <option value="moving">{{ __('bookings.status.moving') }}</option>
                    <option value="arrived">{{ __('bookings.status.arrived') }}</option>
                    <option value="completed">{{ __('bookings.status.completed') }}</option>
                    <option value="cancelled">{{ __('bookings.status.cancelled') }}</option>
                </select>
            </div>

            <div class="col-lg-2">
                <select id="time_period" class="form-select">
                    <option value="">{{ __('bookings.filters.time_period_placeholder') }}</option>
                    <option value="morning">{{ __('bookings.time_period.morning') }}</option>
                    <option value="evening">{{ __('bookings.time_period.evening') }}</option>
                    <option value="all">{{ __('bookings.time_period.all') }}</option>
                </select>
            </div>

            <div class="col-lg-2">
                <input type="date" id="from" class="form-control" placeholder="From">
            </div>

            <div class="col-lg-2">
                <input type="date" id="to" class="form-control" placeholder="To">
            </div>

            <div class="col-lg-3">
                <select id="service_id" class="form-select">
                    <option value="">{{ __('bookings.filters.service_placeholder') }}</option>
                    @foreach($services as $s)
                        @php
                            $name = is_array($s->name) ? ($s->name[app()->getLocale()] ?? collect($s->name)->first()) : $s->name;
                        @endphp
                        <option value="{{ $s->id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3">
                <input type="number" id="employee_id" class="form-control"
                       placeholder="{{ __('bookings.filters.employee_placeholder') }}">
            </div>

            <div class="col-lg-3">
                <input type="number" id="zone_id" class="form-control"
                       placeholder="{{ __('bookings.filters.zone_placeholder') }}">
            </div>

            <div class="col-lg-3">
                <button type="button" id="reset_filters" class="btn btn-light w-100">
                    <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    {{ __('bookings.filters.reset') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="bookings_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>#</th>
                    <th>{{ __('bookings.columns.customer') }}</th>
                    <th>{{ __('bookings.columns.service') }}</th>
                    <th>{{ __('bookings.columns.schedule') }}</th>
                    <th>{{ __('bookings.columns.employee') }}</th>
                    <th>{{ __('bookings.columns.total') }}</th>
                    <th>{{ __('bookings.columns.status_control') }}</th>
                    <th class="text-end">{{ __('bookings.columns.actions') }}</th>
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

    const table = $('#bookings_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.bookings.datatable') }}",
            data: function (d) {
                d.search_custom = $('#search_custom').val();
                d.status = $('#status').val();
                d.time_period = $('#time_period').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
                d.service_id = $('#service_id').val();
                d.employee_id = $('#employee_id').val();
                d.zone_id = $('#zone_id').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'id', name: 'id'},
            {data: 'customer', name: 'user_id', orderable: false, searchable: false},
            {data: 'service_name', name: 'service_id', orderable: false, searchable: false},
            {data: 'schedule', name: 'booking_date', orderable: true, searchable: false},
            {data: 'employee_label', name: 'employee_id', orderable: false, searchable: false},
            {data: 'total', name: 'total_snapshot', searchable: false},
            {data: 'status_control', name: 'status', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end'},
        ],
        drawCallback: function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#search_custom').on('keyup', function () { table.ajax.reload(); });
    $('#status, #time_period, #from, #to, #service_id, #employee_id, #zone_id').on('change keyup', function () {
        table.ajax.reload();
    });

    $('#reset_filters').on('click', function () {
        $('#search_custom').val('');
        $('#status').val('');
        $('#time_period').val('');
        $('#from').val('');
        $('#to').val('');
        $('#service_id').val('');
        $('#employee_id').val('');
        $('#zone_id').val('');
        table.ajax.reload();
    });

    // ✅ change status ajax
    $(document).on('change', '.js-booking-status-select', function () {
        const $select = $(this);
        const url = $select.data('url');
        const status = $select.val();

        $.ajax({
            url: url,
            type: 'PATCH',
            data: {
                _token: "{{ csrf_token() }}",
                status: status
            },
            success: function (res) {
                if (res && res.ok) {
                    // الأفضل: reload نفس الصفحة بدون ما يرجع لأول صفحة
                    table.ajax.reload(null, false);

                    // optional toast
                    if (window.toastr) toastr.success(res.message || "{{ __('bookings.status_updated') }}");
                } else {
                    table.ajax.reload(null, false);
                    if (window.toastr) toastr.error(res.message || 'Error');
                }
            },
            error: function (xhr) {
                table.ajax.reload(null, false);
                const msg = xhr.responseJSON?.message || "{{ __('bookings.status_update_failed') }}";
                if (window.toastr) toastr.error(msg);
            }
        });
    });

})();
</script>
@endpush