@extends('base.layout.app')

@section('title', __('zones.view'))

@push('custom-style')
<style>
    #zone_show_map {
        width: 100%;
        height: 350px;
        border-radius: 0.75rem;
        border: 1px solid #E4E6EF;
        overflow: hidden;
    }
</style>
@endpush

@section('content')

@section('top-btns')
    @can('zones.edit')
        <a href="{{ route('dashboard.zones.edit', $zone->id) }}" class="btn btn-primary">
            {{ __('zones.edit_zone') }}
        </a>
    @endcan
@endsection

<div class="card mb-6">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">

            <div class="d-flex align-items-center gap-3">
                <div class="symbol symbol-50px symbol-circle bg-light-primary">
                    <div class="symbol-label">
                        <i class="ki-duotone ki-map fs-2x text-primary">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <h2 class="mb-0">{{ $zone->name }}</h2>
                    <div class="text-muted fs-7">
                        #{{ $zone->id }} • {{ __('zones.fields.sort_order') }}: {{ (int)$zone->sort_order }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if($zone->is_active)
                    <span class="badge badge-light-success">{{ __('zones.active') }}</span>
                @else
                    <span class="badge badge-light-danger">{{ __('zones.inactive') }}</span>
                @endif

                <span class="badge badge-light-info">
                    {{ __('zones.service_prices.count') }}: {{ $zone->servicePrices->count() }}
                </span>
            </div>

        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        {{-- Tabs header --}}
        <div class="card-header border-0">
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_general">
                        {{ __('zones.tabs.general') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_service_prices">
                        {{ __('zones.tabs.service_prices') }}
                        <span class="badge badge-light-primary ms-2">{{ $zone->servicePrices->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Tabs content --}}
        <div class="tab-content p-6">

            {{-- GENERAL TAB --}}
            <div class="tab-pane fade show active" id="tab_general">
                <div class="row g-6">
                    <div class="col-lg-12">
                        <div class="card card-flush h-100">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('zones.map') }}</h3>
                            </div>
                            <div class="card-body">
                                <div id="zone_show_map"></div>
                                <div class="form-text mt-3">
                                    {{ __('zones.map_hint_show') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SERVICE PRICES TAB --}}
            <div class="tab-pane fade" id="tab_service_prices">

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-5">
                    <div>
                        <h3 class="mb-1">{{ __('zones.tabs.service_prices') }}</h3>
                        <div class="text-muted fs-7">{{ __('zones.service_prices.hint') }}</div>
                    </div>

                    @can('zones.edit')
                        <button type="button" class="btn btn-primary" id="btn_add_sp">
                            <i class="ki-duotone ki-plus fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            {{ __('zones.service_prices.add') }}
                        </button>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th>{{ __('zones.service_prices.service') }}</th>
                            <th>{{ __('zones.service_prices.time_period') }}</th>
                            <th>{{ __('zones.service_prices.price') }}</th>
                            <th>{{ __('zones.service_prices.discounted_price') }}</th>
                            <th>{{ __('zones.service_prices.status') }}</th>
                            <th>{{ __('zones.created_at') }}</th>
                            <th class="text-end">{{ __('zones.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody id="sp_tbody">
                        @forelse($zone->servicePrices as $sp)
                            @include('dashboard.zones.partials._service_price_row', ['sp' => $sp])
                        @empty
                            <tr id="sp_empty_row">
                                <td colspan="8" class="text-center text-muted py-10">
                                    {{ __('zones.service_prices.empty') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="sp_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="sp_modal_title">{{ __('zones.service_prices.add') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <form id="sp_form">
                @csrf
                <input type="hidden" id="sp_id" value="">
                <input type="hidden" id="sp_method" value="POST">

                <div class="modal-body">
                    <div id="sp_form_result" class="alert d-none"></div>

                    <div class="row g-5">

                        <div class="col-12">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('zones.service_prices.service') }}</label>
                            <select id="service_id" class="form-select" data-control="select2"></select>
                            <div class="invalid-feedback d-block" id="err_service_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('zones.service_prices.time_period') }}</label>
                            <select id="time_period" class="form-select">
                                <option value="all">{{ __('zones.time_periods.all') }}</option>
                                <option value="morning">{{ __('zones.time_periods.morning') }}</option>
                                <option value="evening">{{ __('zones.time_periods.evening') }}</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err_time_period"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2 d-block">{{ __('zones.service_prices.status') }}</label>
                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" checked>
                                <label class="form-check-label fw-semibold">{{ __('zones.active') }}</label>
                            </div>
                            <div class="invalid-feedback d-block" id="err_is_active"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('zones.service_prices.price') }}</label>
                            <input type="number" step="0.01" min="0" id="price" class="form-control" value="0">
                            <div class="invalid-feedback d-block" id="err_price"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">{{ __('zones.service_prices.discounted_price') }}</label>
                            <input type="number" step="0.01" min="0" id="discounted_price" class="form-control" placeholder="—">
                            <div class="invalid-feedback d-block" id="err_discounted_price"></div>
                        </div>

                        <div class="col-12">
                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 align-items-center">
                                <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <div class="fw-semibold fs-6 text-gray-700">
                                    {{ __('zones.service_prices.unique_notice') }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ __('zones.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="sp_submit_btn">
                        {{ __('zones.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('custom-script')
<script>
(function () {
    const isAr = document.documentElement.lang === 'ar';

    const modalEl = document.getElementById('sp_modal');
    const modal = new bootstrap.Modal(modalEl);

    const $modal = $('#sp_modal');
    const $service = $('#service_id');

    function clearErrors() {
        $('#sp_form_result').addClass('d-none').removeClass('alert-danger alert-success').html('');
        $('#err_service_id,#err_time_period,#err_price,#err_discounted_price,#err_is_active').text('');
    }

    function safeDestroySelect2($el) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
    }

    // ✅ Select2 Ajax (FIXED for modal search)
    function initServiceSelect2(selected) {
        const url = "{{ route('dashboard.zones.service_prices.search.services', $zone->id) }}";

        // حماية من double init
        safeDestroySelect2($service);

        $service.select2({
            width: '100%',
            dir: isAr ? 'rtl' : 'ltr',
            placeholder: isAr ? 'اختر خدمة...' : 'Select service...',
            allowClear: true,
            minimumInputLength: 0,

            // ✅ أهم سطر: خلي dropdown/search داخل المودال (حل مشكلة عدم الكتابة)
            dropdownParent: $modal,

            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '',
                        time_period: $('#time_period').val(),
                        ignore_service_id: selected ? selected.id : 0
                    };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                cache: true
            }
        });

        // لو selected موجود (edit)
        if (selected && selected.id) {
            const opt = new Option(selected.text, selected.id, true, true);
            $service.append(opt).trigger('change');
        }

        // ✅ منع تكرار handlers + focus داخل المودال نفسه
        $service.off('select2:open.zone').on('select2:open.zone', function () {
            setTimeout(function () {
                const $search = $modal.find('.select2-container--open .select2-search__field');
                if ($search.length) {
                    $search.trigger('focus');
                    if (($search.val() || '') === '') $search.trigger('input');
                }
            }, 0);
        });
    }

    function resetModalForCreate() {
        clearErrors();

        $('#sp_id').val('');
        $('#sp_method').val('POST');
        $('#sp_modal_title').text("{{ __('zones.service_prices.add') }}");

        $('#time_period').val('all');
        $('#price').val(0);
        $('#discounted_price').val('');
        $('#is_active').prop('checked', true);

        $service.empty().trigger('change');

        initServiceSelect2(null);
    }

    function setModalForEdit(data) {
        clearErrors();

        $('#sp_id').val(data.id);
        $('#sp_method').val('PUT');
        $('#sp_modal_title').text("{{ __('zones.service_prices.edit') }}");

        $('#time_period').val(data.time_period);
        $('#price').val(data.price ?? 0);
        $('#discounted_price').val(data.discounted_price ?? '');
        $('#is_active').prop('checked', !!data.is_active);

        $service.empty().trigger('change');

        initServiceSelect2({ id: data.service_id, text: data.service_text });
    }

    // When time_period changes in modal => refresh select2
    $('#time_period').on('change', function () {
        const selectedVal = $service.val();
        const selectedText = $service.find('option:selected').text();
        const selected = selectedVal ? { id: selectedVal, text: selectedText } : null;

        $service.empty().trigger('change');
        initServiceSelect2(selected);
    });

    // ADD
    $('#btn_add_sp').on('click', function () {
        resetModalForCreate();
        modal.show();
    });

    // EDIT
    $(document).on('click', '.btn-edit-sp', function () {
        const id = $(this).data('id');
        if (!id) return;

        clearErrors();

        $.get("{{ route('dashboard.zones.service_prices.show', [$zone->id, 'SP_ID']) }}".replace('SP_ID', id))
            .done(function (res) {
                if (res && res.ok) {
                    setModalForEdit(res.data);
                    modal.show();
                }
            })
            .fail(function () {
                Swal.fire('Error', 'Failed to load record', 'error');
            });
    });

    // DELETE
    $(document).on('click', '.btn-delete-sp', function () {
        const id = $(this).data('id');
        if (!id) return;

        Swal.fire({
            icon: 'warning',
            title: "{{ __('zones.are_you_sure') }}",
            text: "{{ __('zones.service_prices.delete_confirm') }}",
            showCancelButton: true,
            confirmButtonText: "{{ __('zones.delete') }}",
            cancelButtonText: "{{ __('zones.cancel') }}",
        }).then((r) => {
            if (!r.isConfirmed) return;

            $.ajax({
                url: "{{ route('dashboard.zones.service_prices.destroy', [$zone->id, 'SP_ID']) }}".replace('SP_ID', id),
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function (res) {
                    if (res && res.ok) {
                        $('#sp_row_' + id).remove();

                        if ($('#sp_tbody tr').length === 0) {
                            $('#sp_tbody').html(`
                                <tr id="sp_empty_row">
                                    <td colspan="8" class="text-center text-muted py-10">
                                        {{ __('zones.service_prices.empty') }}
                                    </td>
                                </tr>
                            `);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: "{{ __('zones.done') }}",
                            text: res.message || 'Deleted',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Unexpected error', 'error');
                }
            });
        });
    });

    // SUBMIT (Create/Update)
    $('#sp_form').on('submit', function (e) {
        e.preventDefault();

        clearErrors();

        const id = $('#sp_id').val();
        const method = $('#sp_method').val();

        let url = "{{ route('dashboard.zones.service_prices.store', $zone->id) }}";
        if (method === 'PUT') {
            url = "{{ route('dashboard.zones.service_prices.update', [$zone->id, 'SP_ID']) }}".replace('SP_ID', id);
        }

        const payload = {
            _token: "{{ csrf_token() }}",
            _method: method,
            service_id: $service.val(),
            time_period: $('#time_period').val(),
            price: $('#price').val(),
            discounted_price: $('#discounted_price').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0,
        };

        $.ajax({
            url: url,
            type: 'POST',
            data: payload,
            success: function (res) {
                if (!res || !res.ok) return;

                $('#sp_empty_row').remove();

                if (method === 'POST') {
                    $('#sp_tbody').prepend(res.row_html);
                } else {
                    $('#sp_row_' + id).replaceWith(res.row_html);
                }

                modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: "{{ __('zones.done') }}",
                    text: res.message || 'Saved',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;

                    if (errors.service_id?.length) $('#err_service_id').text(errors.service_id[0]);
                    if (errors.time_period?.length) $('#err_time_period').text(errors.time_period[0]);
                    if (errors.price?.length) $('#err_price').text(errors.price[0]);
                    if (errors.discounted_price?.length) $('#err_discounted_price').text(errors.discounted_price[0]);
                    if (errors.is_active?.length) $('#err_is_active').text(errors.is_active[0]);
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Unexpected error', 'error');
                }
            }
        });
    });

    // cleanup select2 when modal hidden
    modalEl.addEventListener('hidden.bs.modal', function () {
        safeDestroySelect2($service);
        $service.empty().trigger('change');
    });

})();
</script>

<script>
(function () {
    // -------------------------------
    // 🗺️ Show map polygon (readonly)
    // -------------------------------
    const polygonCoords = @json($zone->polygon ?? []);
    const centerLat = {{ $zone->center_lat ?? 'null' }};
    const centerLng = {{ $zone->center_lng ?? 'null' }};

    window.initZoneShowMap = function () {
        const mapEl = document.getElementById('zone_show_map');
        if (!mapEl) return;

        const fallbackCenter = { lat: 26.35, lng: 50.08 };
        const center = (centerLat && centerLng) ? { lat: Number(centerLat), lng: Number(centerLng) } : fallbackCenter;

        const map = new google.maps.Map(mapEl, {
            center: center,
            zoom: 12,
        });

        if (Array.isArray(polygonCoords) && polygonCoords.length >= 3) {
            const path = polygonCoords.map(p => ({ lat: Number(p.lat), lng: Number(p.lng) }));

            const poly = new google.maps.Polygon({
                paths: path,
                draggable: false,
                editable: false,
                fillColor: '#0d6efd',
                fillOpacity: 0.20,
                strokeColor: '#0d6efd',
                strokeWeight: 2,
            });

            poly.setMap(map);

            const bounds = new google.maps.LatLngBounds();
            path.forEach(p => bounds.extend(p));
            map.fitBounds(bounds);
        }
    };
})();
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initZoneShowMap"
    async defer></script>
@endpush