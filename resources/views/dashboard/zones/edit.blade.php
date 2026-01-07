@extends('base.layout.app')

@section('title', __('zones.edit'))

@push('custom-style')
    <style>
        #zone_map {
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
    <a href="{{ route('dashboard.zones.index') }}" class="btn btn-light">
        {{ __('zones.back_to_list') }}
    </a>
@endsection

<div class="card">
    <form id="zone_form" action="{{ route('dashboard.zones.update', $zone) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                <div class="col-lg-8">

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">{{ __('zones.edit') }}</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ __('zones.basic_data_hint') }}</span>
                            </h3>
                        </div>

                        <div class="card-body pt-0">
                            <div class="row g-6">

                                <div class="col-md-8 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">{{ __('zones.fields.name') }}</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name', $zone->name) }}"
                                           placeholder="{{ __('zones.placeholders.name') }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('zones.fields.sort_order') }}</label>
                                    <input type="number" min="0" name="sort_order" class="form-control"
                                           value="{{ old('sort_order', $zone->sort_order ?? 0) }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- 🗺️ Zone Polygon --}}
                                <div class="col-12 fv-row">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="fw-semibold fs-6 mb-0">{{ __('zones.fields.polygon') }}</label>

                                        <button type="button" class="btn btn-sm btn-light-danger" id="btn_clear_polygon">
                                            <i class="ki-duotone ki-trash fs-4 me-1">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                            {{ app()->getLocale() === 'ar' ? 'مسح المضلع' : 'Clear polygon' }}
                                        </button>
                                    </div>

                                    <input type="hidden" name="polygon" id="zone_polygon"
                                           value="{{ old('polygon', $zone->polygon ? json_encode($zone->polygon) : '') }}">

                                    <div id="zone_map" class="mb-3"></div>

                                    <div class="invalid-feedback d-block" id="polygon_error"></div>

                                    <div class="form-text">
                                        {{ app()->getLocale() === 'ar'
                                            ? 'يمكنك تعديل نقاط المضلع بالسحب أو مسحه وإعادة رسمه.'
                                            : 'You can drag points to edit, or clear and redraw.' }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <label class="fw-semibold fs-6 mb-2 d-block">{{ __('zones.fields.status') }}</label>
                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       {{ old('is_active', $zone->is_active ? 1 : 0) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">{{ __('zones.active') }}</label>
                            </div>
                            <div class="invalid-feedback d-block"></div>

                            <div class="separator my-6"></div>

                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4">
                                <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-semibold">
                                        <div class="fs-6 text-gray-700">
                                            {{ __('zones.auto_bbox_notice') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('zones.save') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('custom-script')
<script>
(function () {
    const isAr = document.documentElement.lang === 'ar';
    const $form = $('#zone_form');

    $form.on('submit', function(e) {
        e.preventDefault();

        const polygonErrorEl = document.getElementById('polygon_error');
        if (polygonErrorEl) polygonErrorEl.textContent = '';

        const formData = new FormData($form[0]);

        if (window.KH && typeof window.KH.setFormLoading === 'function') {
            window.KH.setFormLoading($form, true, { text: isAr ? 'جاري الحفظ...' : 'Saving...' });
        }

        $.ajax({
            url: $form.attr('action'),
            type: 'POST', // مع _method=PUT
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('zones.done') }}",
                    text: res.message || "{{ __('zones.updated_successfully') }}",
                    timer: 2000,
                    showConfirmButton: false
                });

                if (res.redirect) window.location.href = res.redirect;
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                        window.KH.showValidationErrors($form, xhr.responseJSON.errors, { globalAlertSelector: '#form_result' });
                    }

                    const polygonErrors = xhr.responseJSON.errors.polygon;
                    if (polygonErrors && polygonErrors.length && polygonErrorEl) {
                        polygonErrorEl.textContent = polygonErrors[0];
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Unexpected error', 'error');
                }
            },
            complete: function() {
                if (window.KH && typeof window.KH.setFormLoading === 'function') {
                    window.KH.setFormLoading($form, false);
                }
            }
        });
    });

    // -------------------------------
    // 🗺️ Google Maps + Drawing Manager
    // -------------------------------
    let map, drawingManager, currentPolygon = null;

    window.initZoneMap = function() {
        const mapEl = document.getElementById('zone_map');
        if (!mapEl) return;

        const initialCenter = { lat: 26.35, lng: 50.08 };

        map = new google.maps.Map(mapEl, {
            center: initialCenter,
            zoom: 12,
        });

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: ['polygon']
            },
            polygonOptions: {
                draggable: false,
                editable: true,
                fillColor: '#0d6efd',
                fillOpacity: 0.2,
                strokeColor: '#0d6efd',
                strokeWeight: 2,
            }
        });

        drawingManager.setMap(map);

        loadExistingPolygon();

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
            if (e.type !== google.maps.drawing.OverlayType.POLYGON) return;

            if (currentPolygon) currentPolygon.setMap(null);

            currentPolygon = e.overlay;
            drawingManager.setDrawingMode(null);

            attachPolygonListeners(currentPolygon);
            savePolygonToInput();
        });

        const clearBtn = document.getElementById('btn_clear_polygon');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (currentPolygon) {
                    currentPolygon.setMap(null);
                    currentPolygon = null;
                }
                const hidden = document.getElementById('zone_polygon');
                if (hidden) hidden.value = '';
            });
        }
    };

    function attachPolygonListeners(polygon) {
        const path = polygon.getPath();
        google.maps.event.addListener(path, 'set_at', savePolygonToInput);
        google.maps.event.addListener(path, 'insert_at', savePolygonToInput);
        google.maps.event.addListener(path, 'remove_at', savePolygonToInput);
    }

    function savePolygonToInput() {
        const hidden = document.getElementById('zone_polygon');
        if (!hidden) return;

        if (!currentPolygon) {
            hidden.value = '';
            return;
        }

        const path = currentPolygon.getPath();
        const coords = [];
        for (let i = 0; i < path.getLength(); i++) {
            const p = path.getAt(i);
            coords.push({ lat: p.lat(), lng: p.lng() });
        }
        hidden.value = JSON.stringify(coords);
    }

    function loadExistingPolygon() {
        const hidden = document.getElementById('zone_polygon');
        if (!hidden || !hidden.value) return;

        try {
            const coords = JSON.parse(hidden.value);
            if (!Array.isArray(coords) || coords.length < 3) return;

            const path = coords.map(c => ({ lat: c.lat, lng: c.lng }));

            currentPolygon = new google.maps.Polygon({
                paths: path,
                draggable: false,
                editable: true,
                fillColor: '#0d6efd',
                fillOpacity: 0.2,
                strokeColor: '#0d6efd',
                strokeWeight: 2,
            });

            currentPolygon.setMap(map);
            attachPolygonListeners(currentPolygon);

            const bounds = new google.maps.LatLngBounds();
            path.forEach(p => bounds.extend(p));
            map.fitBounds(bounds);

        } catch (e) {
            console.warn('Invalid polygon JSON', e);
        }
    }

})();
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=drawing&callback=initZoneMap"
    async defer></script>
@endpush