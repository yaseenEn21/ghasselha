@extends('base.layout.app')

@section('title', __('zones.show'))

@push('custom-style')
    <style>
        #zone_map {
            width: 100%;
            height: 420px;
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
            {{ __('zones.edit') }}
        </a>
    @endcan
@endsection

@php
    $polygonJson = $zone->polygon ? json_encode($zone->polygon, JSON_UNESCAPED_UNICODE) : '';
@endphp

<div class="row g-6">

    {{-- Left: Info --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <div class="card-body">

                <div class="d-flex align-items-center mb-5">
                    <div class="symbol symbol-45px symbol-circle me-4">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-map fs-2 text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-bold fs-4">{{ $zone->name }}</span>
                        <span class="text-muted fw-semibold">
                            {{ __('zones.id_label') }}: #{{ $zone->id }}
                        </span>
                    </div>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('zones.fields.status') }}</span>
                    @if($zone->is_active)
                        <span class="badge badge-light-success">{{ __('zones.active') }}</span>
                    @else
                        <span class="badge badge-light-danger">{{ __('zones.inactive') }}</span>
                    @endif
                </div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('zones.fields.sort_order') }}</span>
                    <span class="fw-semibold">{{ $zone->sort_order }}</span>
                </div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('zones.fields.prices_count') }}</span>
                    <span class="fw-semibold">{{ (int) ($zone->service_prices_count ?? 0) }}</span>
                </div>

                <div class="separator my-4"></div>

                <div class="d-flex flex-stack mb-3">
                    <span class="text-muted">{{ __('zones.fields.created_at') }}</span>
                    <span class="fw-semibold">{{ $zone->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                </div>

                <div class="d-flex flex-stack">
                    <span class="text-muted">{{ __('zones.fields.updated_at') }}</span>
                    <span class="fw-semibold">{{ $zone->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="d-flex align-items-center mb-4">
                    <i class="ki-duotone ki-geolocation fs-2tx text-success me-3">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold fs-5">{{ __('zones.location_details') }}</div>
                </div>

                <div class="d-flex flex-column gap-3">

                    <div class="d-flex flex-stack bg-light rounded p-3">
                        <span class="text-muted">{{ __('zones.fields.center') }}</span>
                        <span class="fw-semibold">
                            {{ $zone->center_lat !== null ? ($zone->center_lat.', '.$zone->center_lng) : '—' }}
                        </span>
                    </div>

                    <div class="bg-light rounded p-3">
                        <div class="text-muted mb-1">{{ __('zones.fields.bbox') }}</div>
                        <div class="fw-semibold">
                            {{ $zone->min_lat !== null ? ($zone->min_lat.', '.$zone->min_lng.' → '.$zone->max_lat.', '.$zone->max_lng) : '—' }}
                        </div>
                    </div>

                    <div class="d-flex flex-stack bg-light rounded p-3">
                        <span class="text-muted">{{ __('zones.fields.polygon') }}</span>
                        @if(is_array($zone->polygon) && count($zone->polygon) >= 3)
                            <span class="badge badge-light-success">{{ __('zones.has_polygon') }}</span>
                        @else
                            <span class="badge badge-light-warning">{{ __('zones.no_polygon') }}</span>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Right: Map + Tabs --}}
    <div class="col-lg-8">

        {{-- Map --}}
        <div class="card mb-6">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title fw-bold fs-3 mb-1">{{ __('zones.map_title') }}</h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light" id="btn_fit_bounds">
                        <i class="ki-duotone ki-focus fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        {{ __('zones.fit_bounds') }}
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <input type="hidden" id="zone_polygon" value='{{ $polygonJson }}'>
                <div id="zone_map"></div>

                @if(!is_array($zone->polygon) || count($zone->polygon) < 3)
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mt-5">
                        <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="fw-semibold text-gray-700">
                            {{ __('zones.no_polygon_notice') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card">
            <div class="card-header border-0 pt-5">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x fs-6 fw-bold">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_general">
                            {{ __('zones.tabs.general') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_prices">
                            {{ __('zones.tabs.service_prices') }}
                            <span class="badge badge-light-primary ms-2">{{ (int) ($zone->service_prices_count ?? 0) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    {{-- General tab --}}
                    <div class="tab-pane fade show active" id="tab_general" role="tabpanel">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center bg-light rounded p-4">
                                    <i class="ki-duotone ki-note-2 fs-2tx text-primary me-4">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted fw-semibold">{{ __('zones.fields.name') }}</span>
                                        <span class="fw-bold fs-5">{{ $zone->name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center bg-light rounded p-4">
                                    <i class="ki-duotone ki-abstract-26 fs-2tx text-success me-4">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted fw-semibold">{{ __('zones.fields.status') }}</span>
                                        <span class="fw-bold fs-5">
                                            {{ $zone->is_active ? __('zones.active') : __('zones.inactive') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-6"></div>

                        <div class="text-muted">
                            {{ __('zones.general_hint') }}
                        </div>
                    </div>

                    {{-- Prices tab --}}
                    <div class="tab-pane fade" id="tab_prices" role="tabpanel">
                        @if($zone->servicePrices->count() === 0)
                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                                <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <div class="fw-semibold text-gray-700">
                                    {{ __('zones.no_prices_notice') }}
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-4">
                                    <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th>#</th>
                                        <th>{{ __('zones.prices.service') }}</th>
                                        <th>{{ __('zones.prices.time_period') }}</th>
                                        <th>{{ __('zones.prices.price') }}</th>
                                        <th>{{ __('zones.prices.discounted_price') }}</th>
                                        <th>{{ __('zones.prices.status') }}</th>
                                        <th>{{ __('zones.prices.created_at') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-semibold">
                                    @foreach($zone->servicePrices as $row)
                                        <tr>
                                            <td>{{ $row->id }}</td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">
                                                        {{ $row->service ? i18n($row->service->name) : '—' }}
                                                    </span>
                                                    @if($row->service)
                                                        <span class="text-muted fs-7">
                                                            {{ __('zones.prices.service_id') }}: #{{ $row->service->id }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                <span class="badge badge-light-info">
                                                    {{ __('zones.time_period.' . ($row->time_period ?? 'all')) }}
                                                </span>
                                            </td>

                                            <td>{{ number_format((float) $row->price, 2) }}</td>
                                            <td>{{ $row->discounted_price !== null ? number_format((float) $row->discounted_price, 2) : '—' }}</td>

                                            <td>
                                                @if($row->is_active)
                                                    <span class="badge badge-light-success">{{ __('zones.active') }}</span>
                                                @else
                                                    <span class="badge badge-light-danger">{{ __('zones.inactive') }}</span>
                                                @endif
                                            </td>

                                            <td>{{ $row->created_at?->format('Y-m-d') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('custom-script')
<script>
(function () {
    let map, polygonShape = null;

    window.initZoneShowMap = function () {
        const mapEl = document.getElementById('zone_map');
        if (!mapEl) return;

        const initialCenter = { lat: 26.35, lng: 50.08 };

        map = new google.maps.Map(mapEl, {
            center: initialCenter,
            zoom: 12,
        });

        drawPolygonFromHidden();

        const fitBtn = document.getElementById('btn_fit_bounds');
        if (fitBtn) {
            fitBtn.addEventListener('click', function () {
                fitBounds();
            });
        }
    };

    function drawPolygonFromHidden() {
        const hidden = document.getElementById('zone_polygon');
        if (!hidden || !hidden.value) return;

        try {
            const coords = JSON.parse(hidden.value);
            if (!Array.isArray(coords) || coords.length < 3) return;

            const path = coords.map(c => ({ lat: Number(c.lat), lng: Number(c.lng) }));

            polygonShape = new google.maps.Polygon({
                paths: path,
                draggable: false,
                editable: false,
                fillColor: '#0d6efd',
                fillOpacity: 0.20,
                strokeColor: '#0d6efd',
                strokeWeight: 2,
            });

            polygonShape.setMap(map);
            fitBounds(path);

        } catch (e) {
            console.warn('Invalid polygon JSON', e);
        }
    }

    function fitBounds(pathOverride = null) {
        if (!map) return;

        let path = pathOverride;

        if (!path && polygonShape) {
            const p = polygonShape.getPath();
            path = [];
            for (let i = 0; i < p.getLength(); i++) {
                const pt = p.getAt(i);
                path.push({lat: pt.lat(), lng: pt.lng()});
            }
        }

        if (!path || path.length < 3) return;

        const bounds = new google.maps.LatLngBounds();
        path.forEach(p => bounds.extend(p));
        map.fitBounds(bounds);
    }
})();
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initZoneShowMap"
    async defer></script>
@endpush