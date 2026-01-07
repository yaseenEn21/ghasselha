@extends('base.layout.app')

@section('content')

<div class="card">
    <div class="card-body">

        <div class="row g-4 mb-6">
            <div class="col-md-3">
                <input type="text" id="search_custom" class="form-control"
                       placeholder="{{ __('payments.filters.search_placeholder') }}">
            </div>

            <div class="col-md-2">
                <select id="status" class="form-select">
                    <option value="">{{ __('payments.filters.status_placeholder') }}</option>
                    <option value="pending">{{ __('payments.status.pending') }}</option>
                    <option value="paid">{{ __('payments.status.paid') }}</option>
                    <option value="failed">{{ __('payments.status.failed') }}</option>
                    <option value="cancelled">{{ __('payments.status.cancelled') }}</option>
                    <option value="refunded">{{ __('payments.status.refunded') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="method" class="form-select">
                    <option value="">{{ __('payments.filters.method_placeholder') }}</option>
                    <option value="wallet">{{ __('payments.method.wallet') }}</option>
                    <option value="credit_card">{{ __('payments.method.credit_card') }}</option>
                    <option value="apple_pay">{{ __('payments.method.apple_pay') }}</option>
                    <option value="google_pay">{{ __('payments.method.google_pay') }}</option>
                    <option value="cash">{{ __('payments.method.cash') }}</option>
                    <option value="visa">{{ __('payments.method.visa') }}</option>
                    <option value="stc">{{ __('payments.method.stc') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" id="from" class="form-control" title="{{ __('payments.filters.from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" id="to" class="form-control" title="{{ __('payments.filters.to') }}">
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
            <table id="payments_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>#</th>
                    <th>{{ __('payments.fields.user') }}</th>
                    {{-- <th>{{ __('payments.fields.invoice') }}</th> --}}
                    <th>{{ __('payments.fields.payable') }}</th>
                    <th>{{ __('payments.fields.method') }}</th>
                    <th>{{ __('payments.fields.status') }}</th>
                    <th>{{ __('payments.fields.gateway') }}</th>
                    <th>{{ __('payments.fields.amount') }}</th>
                    <th>{{ __('payments.fields.paid_at') }}</th>
                    <th>{{ __('payments.fields.created_at') }}</th>
                    <th class="text-end">{{ __('payments.actions_title') }}</th>
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
    const table = $('#payments_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboard.payments.datatable') }}",
            data: function (d) {
                d.search_custom = $('#search_custom').val();
                d.status = $('#status').val();
                d.method = $('#method').val();
                d.has_invoice = $('#has_invoice').val();
                d.gateway = $('#gateway').val();
                d.payable_type = $('#payable_type').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'id', name: 'id'},
            {data: 'user_label', name: 'user_id', orderable:false, searchable:false},
            // {data: 'invoice_label', name: 'invoice_id', orderable:false, searchable:false},
            {data: 'payable_label', name: 'payable_id', orderable:false, searchable:false},
            {data: 'method_badge', name: 'method', orderable:false, searchable:false},
            {data: 'status_badge', name: 'status', orderable:false, searchable:false},
            {data: 'gateway_label', name: 'gateway', orderable:false, searchable:false},
            {data: 'amount', name: 'amount', searchable:false},
            {data: 'paid_at_label', name: 'paid_at', orderable:false, searchable:false},
            {data: 'created_at_label', name: 'created_at', orderable:false, searchable:false},
            {data: 'actions', name: 'actions', orderable:false, searchable:false, className:'text-end'},
        ],
        drawCallback: function () {
            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#search_custom, #status, #method, #has_invoice, #gateway, #payable_type, #from, #to').on('keyup change', function () {
        table.ajax.reload();
    });

    $('#reset_filters').on('click', function () {
        $('#search_custom').val('');
        $('#status').val('');
        $('#method').val('');
        $('#has_invoice').val('');
        $('#gateway').val('');
        $('#payable_type').val('');
        $('#from').val('');
        $('#to').val('');
        table.ajax.reload();
    });
})();
</script>
@endpush