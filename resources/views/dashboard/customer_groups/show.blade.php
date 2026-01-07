@extends('base.layout.app')

@section('title', __('customer_groups.show'))

@section('content')

@section('top-btns')
    @can('customer_groups.edit')
        <a href="{{ route('dashboard.customer-groups.edit', $customerGroup->id) }}" class="btn btn-primary">
            {{ __('customer_groups.edit_group') }}
        </a>
    @endcan
@endsection

<div class="row g-6">

    {{-- Left Info --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <div class="card-body">

                <div class="d-flex align-items-center mb-5">
                    <div class="symbol symbol-45px symbol-circle me-4">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-people fs-2 text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </span>
                    </div>

                    <div class="d-flex flex-column">
                        <span class="fw-bold fs-4">{{ $customerGroup->name }}</span>
                        <span class="text-muted fw-semibold">{{ __('customer_groups.id_label') }}:
                            #{{ $customerGroup->id }}</span>
                    </div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('customer_groups.fields.status') }}</span>
                    @if ($customerGroup->is_active)
                        <span class="badge badge-light-success">{{ __('customer_groups.active') }}</span>
                    @else
                        <span class="badge badge-light-danger">{{ __('customer_groups.inactive') }}</span>
                    @endif
                </div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('customer_groups.fields.service_prices_count') }}</span>
                    <span class="fw-semibold"
                        id="service_prices_count">{{ (int) ($customerGroup->service_prices_count ?? 0) }}</span>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('customer_groups.fields.created_at') }}</span>
                    <span class="fw-semibold">{{ $customerGroup->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                </div>

                <div class="d-flex flex-stack">
                    <span class="text-muted">{{ __('customer_groups.fields.updated_at') }}</span>
                    <span class="fw-semibold">{{ $customerGroup->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4">
                    <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="fw-semibold text-gray-700">
                        {{ __('customer_groups.hint') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Tabs --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0 pt-5">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x fs-6 fw-bold">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_general">
                            {{ __('customer_groups.tabs.general') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_prices">
                            {{ __('customer_groups.tabs.service_prices') }}
                            <span class="badge badge-light-primary ms-2"
                                id="service_prices_badge">{{ (int) ($customerGroup->service_prices_count ?? 0) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    {{-- General --}}
                    <div class="tab-pane fade show active" id="tab_general" role="tabpanel">
                        <div class="text-muted">
                            {{ __('customer_groups.general_hint') }}
                        </div>
                    </div>

                    {{-- Prices --}}
                    <div class="tab-pane fade" id="tab_prices" role="tabpanel">

                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-6">
                            <div class="d-flex gap-3">
                                <input type="text" id="search_custom" class="form-control w-250px"
                                    placeholder="{{ __('customer_groups.prices.search_placeholder') }}">

                                <select id="status_filter" class="form-select w-200px">
                                    <option value="">{{ __('customer_groups.filters.status_placeholder') }}
                                    </option>
                                    <option value="active">{{ __('customer_groups.active') }}</option>
                                    <option value="inactive">{{ __('customer_groups.inactive') }}</option>
                                </select>
                            </div>

                            @can('customer_groups.edit')
                                <button type="button" class="btn btn-primary" id="btn_add_service">
                                    <i class="ki-duotone ki-plus fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    {{ __('customer_groups.prices.add_service') }}
                                </button>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table id="service_prices_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        {{-- <th>#</th> --}}
                                        <th>{{ __('customer_groups.prices.service') }}</th>
                                        <th>{{ __('customer_groups.prices.base_price') }}</th>
                                        <th>{{ __('customer_groups.prices.group_price') }}</th>
                                        <th>{{ __('customer_groups.prices.discounted_price') }}</th>
                                        <th>{{ __('customer_groups.prices.status') }}</th>
                                        <th>{{ __('customer_groups.prices.created_at') }}</th>
                                        <th class="text-end">{{ __('customer_groups.prices.actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="service_price_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="service_price_modal_title">
                    {{ __('customer_groups.prices.modal_create_title') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <form id="service_price_form" method="POST">
                @csrf
                <input type="hidden" id="service_price_id" value="">
                <input type="hidden" name="_method" id="service_price_method" value="POST">
                <input type="hidden" name="service_id" id="service_id_hidden" value="">

                <div class="modal-body">
                    <div id="form_result" class="alert d-none"></div>

                    <div class="row g-6">
                        <div class="col-12 fv-row">
                            <label
                                class="required fw-semibold fs-6 mb-2">{{ __('customer_groups.prices.service') }}</label>
                            <select id="service_id_select" class="form-select" data-control="select2"
                                data-placeholder="{{ __('customer_groups.prices.select_service') }}"
                                data-ajax-url="{{ route('dashboard.customer-groups.services.search', $customerGroup->id) }}">
                            </select>
                            <div class="invalid-feedback d-block"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label
                                class="required fw-semibold fs-6 mb-2">{{ __('customer_groups.prices.group_price') }}</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control"
                                value="0">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label
                                class="fw-semibold fs-6 mb-2">{{ __('customer_groups.prices.discounted_price') }}</label>
                            <input type="number" step="0.01" min="0" name="discounted_price"
                                class="form-control" placeholder="—">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                    value="1" checked>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    {{ __('customer_groups.active') }}
                                </label>
                            </div>
                            <div class="invalid-feedback d-block"></div>
                        </div>

                        <div class="col-12">
                            <div class="form-text">
                                {{ __('customer_groups.prices.modal_hint') }}
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">{{ __('customer_groups.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">{{ __('customer_groups.save') }}</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@push('custom-script')
<script>
    (function() {
        const isAr = document.documentElement.lang === 'ar';
        const modalEl = document.getElementById('service_price_modal');
        const modal = new bootstrap.Modal(modalEl);

        const $form = $('#service_price_form');
        const $select = $('#service_id_select');

        const storeUrl = "{{ route('dashboard.customer-groups.service-prices.store', $customerGroup->id) }}";
        const datatableUrl =
            "{{ route('dashboard.customer-groups.service-prices.datatable', $customerGroup->id) }}";

        // --------------------
        // Select2 Ajax
        // --------------------
        function initServiceSelect2(currentServiceId = null) {
            const url = $select.data('ajax-url');
            const $modal = $('#service_price_modal');

            // مهم جدًا: تجنب تهيئة مرتين
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                width: '100%',
                dir: isAr ? 'rtl' : 'ltr',
                placeholder: $select.data('placeholder') || '',
                allowClear: true,
                minimumInputLength: 0,

                // ✅ أهم سطر: خلي dropdown داخل المودال
                dropdownParent: $modal,

                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            current_service_id: currentServiceId || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results || []
                        };
                    },
                    cache: true
                }
            });

            // ✅ لا تكرر event handlers كل مرة (namespaced + off)
            $select.off('select2:open.cg').on('select2:open.cg', function() {
                setTimeout(function() {
                    const $search = $modal.find('.select2-container--open .select2-search__field');
                    if ($search.length) {
                        $search.trigger('focus');
                        if ($search.val() === '') $search.trigger(
                        'input'); // عشان يجيب أول 10 نتائج
                    }
                }, 0);
            });

            $select.off('change.cg').on('change.cg', function() {
                $('#service_id_hidden').val($(this).val() || '');
            });
        }

        // --------------------
        // DataTable
        // --------------------
        const table = $('#service_prices_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: datatableUrl,
                data: function(d) {
                    d.search_custom = $('#search_custom').val();
                    d.status = $('#status_filter').val();
                }
            },
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'service_label',
                    name: 'service.name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'base_price',
                    name: 'service.price',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'group_price',
                    name: 'price',
                    searchable: false
                },
                {
                    data: 'discounted_price',
                    name: 'discounted_price',
                    searchable: false
                },
                {
                    data: 'is_active_badge',
                    name: 'is_active',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at_f',
                    name: 'created_at',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-end'
                },
            ],
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        $('#search_custom, #status_filter').on('keyup change', function() {
            table.ajax.reload();
        });

        // --------------------
        // Open Create Modal
        // --------------------
        $('#btn_add_service').on('click', function() {
            resetModal();

            $('#service_price_modal_title').text("{{ __('customer_groups.prices.modal_create_title') }}");
            $('#service_price_method').val('POST');
            $form.attr('action', storeUrl);

            // select2 rebuild
            $select.prop('disabled', false);
            $select.empty().trigger('change');
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            initServiceSelect2(null);

            modal.show();
        });

        // --------------------
        // Edit Button
        // --------------------
        $(document).on('click', '.btn-edit', function() {
            resetModal();

            const editUrl = $(this).data('edit-url');
            const updateUrl = $(this).data('update-url');

            $('#service_price_modal_title').text("{{ __('customer_groups.prices.modal_edit_title') }}");
            $('#service_price_method').val('PUT');
            $form.attr('action', updateUrl);

            // fetch row
            $.get(editUrl).done(function(res) {
                const d = res.data || {};

                $('#service_price_id').val(d.id || '');
                $('input[name="price"]').val(d.price || 0);
                $('input[name="discounted_price"]').val(d.discounted_price || '');
                $('#is_active').prop('checked', !!d.is_active);

                // Select2: include current service, and lock changing service on edit
                $select.empty().trigger('change');
                if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
                initServiceSelect2(d.service_id);

                if (d.service_id) {
                    const option = new Option(d.service_text || ('#' + d.service_id), d.service_id,
                        true, true);
                    $select.append(option).trigger('change');
                    $('#service_id_hidden').val(d.service_id);
                }

                // prevent changing service in edit
                $select.prop('disabled', true);

                modal.show();
            }).fail(function() {
                Swal.fire('Error', 'Failed to load data', 'error');
            });
        });

        // --------------------
        // Delete Button
        // --------------------
        $(document).on('click', '.btn-delete', function() {
            const deleteUrl = $(this).data('delete-url');

            Swal.fire({
                icon: 'warning',
                title: "{{ __('customer_groups.confirm_title') }}",
                text: "{{ __('customer_groups.prices.confirm_delete_text') }}",
                showCancelButton: true,
                confirmButtonText: "{{ __('customer_groups.delete') }}",
                cancelButtonText: "{{ __('customer_groups.cancel') }}",
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: "{{ __('customer_groups.done') }}",
                            text: res.message ||
                                "{{ __('customer_groups.prices.deleted_successfully') }}",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        updateCounts(res.count);
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message ||
                            'Unexpected error', 'error');
                    }
                });
            });
        });

        // --------------------
        // Submit Modal (Create/Update)
        // --------------------
        $form.on('submit', function(e) {
            e.preventDefault();

            // إذا السيلكت disabled في edit، hidden يحمل القيمة
            const formData = new FormData($form[0]);

            if (window.KH && typeof window.KH.setFormLoading === 'function') {
                window.KH.setFormLoading($form, true, {
                    text: isAr ? 'جاري الحفظ...' : 'Saving...'
                });
            }

            $.ajax({
                url: $form.attr('action'),
                type: 'POST', // method spoofing by _method
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('customer_groups.done') }}",
                        text: res.message ||
                            "{{ __('customer_groups.saved_successfully') }}",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    updateCounts(res.count);
                    modal.hide();
                    table.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                            window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                                globalAlertSelector: '#form_result'
                            });
                        }
                    } else {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Unexpected error',
                            'error');
                    }
                },
                complete: function() {
                    if (window.KH && typeof window.KH.setFormLoading === 'function') {
                        window.KH.setFormLoading($form, false);
                    }
                }
            });
        });

        // --------------------
        // Helpers
        // --------------------
        function resetModal() {
            $('#form_result').addClass('d-none').removeClass('alert-danger alert-success').html('');
            $('#service_price_id').val('');
            $('#service_price_method').val('POST');
            $('#service_id_hidden').val('');

            $('input[name="price"]').val(0);
            $('input[name="discounted_price"]').val('');
            $('#is_active').prop('checked', true);

            // destroy only if initialized
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.empty().prop('disabled', false).trigger('change');
        }

        function updateCounts(count) {
            if (typeof count === 'number') {
                $('#service_prices_count').text(count);
                $('#service_prices_badge').text(count);
            }
        }

    })();
</script>
@endpush
