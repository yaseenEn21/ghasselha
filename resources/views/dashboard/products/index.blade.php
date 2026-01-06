@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('products.create')
        <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">
            {{ __('products.create_new') }}
        </a>
    @endcan
@endsection

<div class="card">
    <div class="card-body">

        <div class="row g-4 mb-6">
            <div class="col-md-4">
                <label class="form-label">{{ __('products.filters.search') }}</label>
                <input type="text" id="search_custom" class="form-control"
                       placeholder="{{ __('products.filters.search_placeholder') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('products.filters.category') }}</label>
                <select id="category_id" class="form-select" data-control="select2">
                    <option value="">{{ __('products.filters.all_categories') }}</option>
                    @php $locale = app()->getLocale(); @endphp
                    @foreach ($categories as $cat)
                        @php $n = $cat->name ?? []; $n = $n[$locale] ?? (reset($n) ?: ''); @endphp
                        <option value="{{ $cat->id }}">{{ $n }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('products.filters.status') }}</label>
                <select id="status" class="form-select" data-control="select2">
                    <option value="">{{ __('products.filters.all_status') }}</option>
                    <option value="active">{{ __('products.active') }}</option>
                    <option value="inactive">{{ __('products.inactive') }}</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-light" id="btn_reset_filters" type="button">
                    {{ __('products.filters.reset') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="products_table">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>#</th>
                    <th>{{ __('products.fields.name') }}</th>
                    <th>{{ __('products.fields.category') }}</th>
                    <th>{{ __('products.fields.price') }}</th>
                    <th>{{ __('products.fields.discounted_price') }}</th>
                    <th>{{ __('products.fields.max_qty_per_booking') }}</th>
                    <th>{{ __('products.fields.status') }}</th>
                    <th>{{ __('products.fields.sort_order') }}</th>
                    <th>{{ __('products.created_at') }}</th>
                    <th>{{ __('products.actions') }}</th>
                </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@push('custom-script')
<script>
    (function () {
        const $table = $('#products_table');

        const dt = $table.DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '{{ route('dashboard.products.index') }}',
                data: function (d) {
                    d.search_custom = $('#search_custom').val();
                    d.category_id = $('#category_id').val();
                    d.status = $('#status').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'category_name', name: 'category.name', orderable: false},
                {data: 'price', name: 'price'},
                {data: 'discounted_price', name: 'discounted_price'},
                {data: 'max_qty_per_booking', name: 'max_qty_per_booking', orderable:false},
                {data: 'is_active_badge', name: 'is_active', orderable:false, searchable:false},
                {data: 'sort_order', name: 'sort_order'},
                {data: 'created_at', name: 'created_at'},
                {data: 'actions', name: 'actions', orderable:false, searchable:false},
            ],
            drawCallback: function () {
                $('.js-delete-product').off('click').on('click', function () {
                    const id = $(this).data('id');
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('products.delete_confirm_title') }}',
                        text: '{{ __('products.delete_confirm_text') }}',
                        showCancelButton: true,
                        confirmButtonText: '{{ __('products.delete') }}',
                        cancelButtonText: '{{ __('products.cancel') }}',
                    }).then((res) => {
                        if (!res.isConfirmed) return;

                        $.ajax({
                            url: '{{ url('/dashboard/products') }}/' + id,
                            type: 'POST',
                            data: {_method: 'DELETE', _token: '{{ csrf_token() }}'},
                            success: function () {
                                dt.ajax.reload(null, false);
                            }
                        });
                    });
                });
            }
        });

        $('#search_custom, #category_id, #status').on('change keyup', function () {
            dt.ajax.reload();
        });

        $('#btn_reset_filters').on('click', function () {
            $('#search_custom').val('');
            $('#category_id').val('').trigger('change');
            $('#status').val('').trigger('change');
            dt.ajax.reload();
        });
    })();
</script>
@endpush