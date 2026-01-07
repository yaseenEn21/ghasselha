@extends('base.layout.app')

@section('content')

@section('top-btns')
    {{-- لاحقًا يمكن إضافة زر Create لو بدك --}}
@endsection

<div class="card">
    <div class="card-body">

        <div class="row g-4 mb-6">
            <div class="col-md-3">
                <input type="text" id="search_custom" class="form-control"
                       placeholder="{{ __('invoices.filters.search_placeholder') }}">
            </div>

            <div class="col-md-2">
                <select id="status" class="form-select">
                    <option value="">{{ __('invoices.filters.status_placeholder') }}</option>
                    <option value="unpaid">{{ __('invoices.status.unpaid') }}</option>
                    <option value="paid">{{ __('invoices.status.paid') }}</option>
                    <option value="cancelled">{{ __('invoices.status.cancelled') }}</option>
                    <option value="refunded">{{ __('invoices.status.refunded') }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <input type="date" id="from" class="form-control" title="{{ __('invoices.filters.from') }}">
            </div>
            <div class="col-md-3">
                <input type="date" id="to" class="form-control" title="{{ __('invoices.filters.to') }}">
            </div>

            <div class="col-md-1">
                <button type="button" id="reset_filters" class="btn btn-light">
                    <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="invoices_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>#</th>
                    <th>{{ __('invoices.fields.number') }}</th>
                    <th>{{ __('invoices.fields.user') }}</th>
                    <th>{{ __('invoices.fields.invoiceable') }}</th>
                    {{-- <th>{{ __('invoices.fields.type') }}</th> --}}
                    <th>{{ __('invoices.fields.status') }}</th>
                    {{-- <th>{{ __('invoices.fields.locked') }}</th> --}}
                    <th>{{ __('invoices.fields.subtotal') }}</th>
                    <th>{{ __('invoices.fields.discount') }}</th>
                    {{-- <th>{{ __('invoices.fields.tax') }}</th> --}}
                    <th>{{ __('invoices.fields.total') }}</th>
                    <th>{{ __('invoices.fields.issued_at') }}</th>
                    <th>{{ __('invoices.fields.paid_at') }}</th>
                    <th class="text-end">{{ __('invoices.actions_title') }}</th>
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
    const table = $('#invoices_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.invoices.datatable') }}",
            data: function (d) {
                d.search_custom = $('#search_custom').val();
                d.status = $('#status').val();
                d.type = $('#type').val();
                d.locked = $('#locked').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'id', name: 'id'},
            {data: 'number', name: 'number'},
            {data: 'user_label', name: 'user_id', orderable:false, searchable:false},
            {data: 'invoiceable_label', name: 'invoiceable_id', orderable:false, searchable:false},
            // {data: 'type_badge', name: 'type', orderable:false, searchable:false},
            {data: 'status_badge', name: 'status', orderable:false, searchable:false},
            // {data: 'locked_badge', name: 'is_locked', orderable:false, searchable:false},
            {data: 'subtotal', name: 'subtotal', searchable:false},
            {data: 'discount', name: 'discount', searchable:false},
            // {data: 'tax', name: 'tax', searchable:false},
            {data: 'total', name: 'total', searchable:false},
            {data: 'issued_at_label', name: 'issued_at', orderable:false, searchable:false},
            {data: 'paid_at_label', name: 'paid_at', orderable:false, searchable:false},
            {data: 'actions', name: 'actions', orderable:false, searchable:false, className:'text-end'},
        ],
        drawCallback: function () {
            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#search_custom, #status, #type, #locked, #from, #to').on('keyup change', function () {
        table.ajax.reload();
    });

    $('#reset_filters').on('click', function () {
        $('#search_custom').val('');
        $('#status').val('');
        $('#type').val('');
        $('#locked').val('');
        $('#from').val('');
        $('#to').val('');
        table.ajax.reload();
    });
})();
</script>
@endpush