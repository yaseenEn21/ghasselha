@extends('base.layout.app')

@section('title', __('employees.show_title', ['name' => $employee->user->name ?? '']))

@push('custom-style')
    <style>
        #employee_work_area_map_show {
            width: 100%;
            height: 320px;
            border-radius: 0.75rem;
            border: 1px solid #E4E6EF;
            overflow: hidden;
        }
    </style>
@endpush

@section('content')

    @section('top-btns')
        @can('employees.edit')
            <a href="{{ route('dashboard.employees.edit', $employee->id) }}" class="btn btn-primary">
                {{ __('employees.edit') }}
            </a>
        @endcan
    @endsection

    {{-- رأس الصفحة: معلومات سريعة عن الموظف --}}
    <div class="card mb-6">
        <div class="card-body d-flex flex-column flex-md-row align-items-start justify-content-between">

            <div class="d-flex align-items-center mb-4 mb-md-0">
                {{-- Avatar بسيط بحرف من الاسم --}}
                <div class="symbol symbol-60px symbol-circle me-4">
                    <span class="symbol-label bg-light-primary text-primary fs-2 fw-bold">
                        {{ mb_substr($employee->user->name ?? '', 0, 1) }}
                    </span>
                </div>

                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center mb-1">
                        <h2 class="fw-bold mb-0 me-2">
                            {{ $employee->user->name ?? '—' }}
                        </h2>

                        {{-- حالة الحساب --}}
                        @if($employee->user->is_active ?? false)
                            <span class="badge badge-light-success fw-semibold me-1">
                                {{ __('employees.status_active') }}
                            </span>
                        @else
                            <span class="badge badge-light-danger fw-semibold me-1">
                                {{ __('employees.status_inactive') }}
                            </span>
                        @endif

                        {{-- نوع المستخدم --}}
                        <span class="badge badge-light-primary fw-semibold">
                            {{ __('employees.biker_badge') }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap text-muted fw-semibold fs-7">
                        <div class="me-5 mb-2">
                            <i class="ki-duotone ki-phone fs-5 me-1 text-gray-500"></i>
                            <span>{{ $employee->user->mobile ?? '—' }}</span>
                        </div>

                        @if(!empty($employee->user->email))
                            <div class="me-5 mb-2">
                                <i class="ki-duotone ki-sms fs-5 me-1 text-gray-500"></i>
                                <span>{{ $employee->user->email }}</span>
                            </div>
                        @endif

                        <div class="mb-2">
                            <i class="ki-duotone ki-notification-status fs-5 me-1 text-gray-500"></i>
                            @if($employee->user->notification ?? false)
                                <span>{{ __('employees.notification_on') }}</span>
                            @else
                                <span>{{ __('employees.notification_off') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- معلومات إضافية على اليمين --}}
            <div class="d-flex flex-column align-items-md-end align-items-start gap-2">
                <div class="text-muted fs-7">
                    {{ __('employees.created_at') }}:
                    <span class="fw-semibold">
                        {{ optional($employee->created_at)->format('Y-m-d H:i') }}
                    </span>
                </div>
                <div class="text-muted fs-7">
                    {{ __('employees.last_update') }}:
                    <span class="fw-semibold">
                        {{ optional($employee->updated_at)->format('Y-m-d H:i') }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- Tabs --}}
    <div class="card">
        <div class="card-body">

            <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_employee_overview">
                        {{ __('employees.tab_overview') }}
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_employee_bookings">
                        {{ __('employees.tab_bookings') }}
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="employee_show_tabs">

                {{-- تبويب: نظرة عامة --}}
                <div class="tab-pane fade show active" id="tab_employee_overview" role="tabpanel">
                    <div class="row g-6">

                        {{-- معلومات أساسية --}}
                        <div class="col-xl-4">
                            <div class="card card-flush h-100">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-4 mb-1">
                                            {{ __('employees.basic_info') }}
                                        </span>
                                        <span class="text-muted mt-1 fw-semibold fs-7">
                                            {{ __('employees.basic_info_hint') }}
                                        </span>
                                    </h3>
                                </div>
                                <div class="card-body pt-0">

                                    <div class="mb-4">
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.name') }}</div>
                                        <div class="fw-semibold">{{ $employee->user->name ?? '—' }}</div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.mobile') }}</div>
                                        <div class="fw-semibold">{{ $employee->user->mobile ?? '—' }}</div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.email') }}</div>
                                        <div class="fw-semibold">{{ $employee->user->email ?? '—' }}</div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.gender') }}</div>
                                        <div class="fw-semibold">
                                            @if(($employee->user->gender ?? '') === 'female')
                                                {{ __('employees.fields.gender_female') }}
                                            @else
                                                {{ __('employees.fields.gender_male') }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.birth_date') }}</div>
                                        <div class="fw-semibold">
                                            {{ optional($employee->user->birth_date)->format('Y-m-d') ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.is_active') }}</div>
                                        <div class="fw-semibold">
                                            @if($employee->is_active)
                                                <span class="badge badge-light-success">{{ __('employees.status_active') }}</span>
                                            @else
                                                <span class="badge badge-light-danger">{{ __('employees.status_inactive') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-muted fs-8 mb-1">{{ __('employees.fields.notification') }}</div>
                                        <div class="fw-semibold">
                                            @if($employee->user->notification ?? false)
                                                <span class="badge badge-light-primary">{{ __('employees.notification_on') }}</span>
                                            @else
                                                <span class="badge badge-light">{{ __('employees.notification_off') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- ساعات العمل + الخدمات + الخريطة --}}
                        <div class="col-xl-8">
                            <div class="row g-6">

                                {{-- ساعات العمل الأسبوعية --}}
                                <div class="col-12">
                                    <div class="card card-flush h-100">
                                        <div class="card-header border-0 pt-5">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="card-label fw-bold fs-4 mb-1">
                                                    {{ __('employees.working_hours_card_title') }}
                                                </span>
                                                <span class="text-muted mt-1 fw-semibold fs-7">
                                                    {{ __('employees.working_hours_card_hint') }}
                                                </span>
                                            </h3>
                                        </div>
                                        <div class="card-body pt-0 table-responsive">

                                            @php
                                                $daysLabels = [
                                                    'saturday'  => app()->getLocale() === 'ar' ? 'السبت' : 'Saturday',
                                                    'sunday'    => app()->getLocale() === 'ar' ? 'الأحد' : 'Sunday',
                                                    'monday'    => app()->getLocale() === 'ar' ? 'الاثنين' : 'Monday',
                                                    'tuesday'   => app()->getLocale() === 'ar' ? 'الثلاثاء' : 'Tuesday',
                                                    'wednesday' => app()->getLocale() === 'ar' ? 'الأربعاء' : 'Wednesday',
                                                    'thursday'  => app()->getLocale() === 'ar' ? 'الخميس' : 'Thursday',
                                                    'friday'    => app()->getLocale() === 'ar' ? 'الجمعة' : 'Friday',
                                                ];
                                            @endphp

                                            <table class="table align-middle table-row-dashed">
                                                <thead>
                                                <tr class="fw-semibold fs-7 text-muted">
                                                    <th>{{ app()->getLocale() === 'ar' ? 'اليوم' : 'Day' }}</th>
                                                    <th>{{ __('employees.working_hours_work') }}</th>
                                                    <th>{{ __('employees.working_hours_break') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($daysLabels as $dayKey => $dayLabel)
                                                    @php
                                                        $work  = $weeklyByDay[$dayKey]['work']  ?? null;
                                                        $break = $weeklyByDay[$dayKey]['break'] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-bold">{{ $dayLabel }}</td>
                                                        <td>
                                                            @if($work && ($work['is_active'] ?? false))
                                                                <span class="badge badge-light-success fw-semibold">
                                                                    {{ $work['start_time'] ?? '--:--' }}
                                                                    –
                                                                    {{ $work['end_time'] ?? '--:--' }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted fs-8">
                                                                    {{ __('employees.off_day') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($break && ($break['is_active'] ?? false))
                                                                <span class="badge badge-light-warning fw-semibold">
                                                                    {{ $break['start_time'] ?? '--:--' }}
                                                                    –
                                                                    {{ $break['end_time'] ?? '--:--' }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted fs-8">
                                                                    {{ __('employees.no_break') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>

                                {{-- الخدمات --}}
                                <div class="col-12">
                                    <div class="card card-flush h-100">
                                        <div class="card-header border-0 pt-5">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="card-label fw-bold fs-4 mb-1">
                                                    {{ __('employees.services_card_title') }}
                                                </span>
                                                <span class="text-muted mt-1 fw-semibold fs-7">
                                                    {{ __('employees.services_card_hint') }}
                                                </span>
                                            </h3>
                                        </div>
                                        <div class="card-body pt-0">
                                            @php $locale = app()->getLocale(); @endphp

                                            @if($employee->services->isEmpty())
                                                <div class="text-muted fs-7">
                                                    {{ __('employees.no_services_assigned') }}
                                                </div>
                                            @else
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($employee->services as $service)
                                                        @php
                                                            $sName = $service->name[$locale] ?? (is_array($service->name) ? (reset($service->name) ?: '') : $service->name);
                                                        @endphp
                                                        <span class="badge badge-light-primary fw-semibold">
                                                            {{ $sName }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- الخريطة --}}
                                <div class="col-12">
                                    <div class="card card-flush h-100">
                                        <div class="card-header border-0 pt-5">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="card-label fw-bold fs-4 mb-1">
                                                    {{ __('employees.work_area_card_title') }}
                                                </span>
                                                <span class="text-muted mt-1 fw-semibold fs-7">
                                                    {{ __('employees.work_area_card_hint') }}
                                                </span>
                                            </h3>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div id="employee_work_area_map_show"></div>

                                            @if(empty($workAreaPolygon))
                                                <div class="text-muted fs-7 mt-3">
                                                    {{ __('employees.no_work_area_defined') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- تبويب: الحجوزات (جاهز للمستقبل) --}}
                <div class="tab-pane fade" id="tab_employee_bookings" role="tabpanel">
                    <div class="alert alert-info d-flex align-items-center p-5">
                        <i class="ki-duotone ki-calendar-search fs-2hx me-4 text-info">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1">
                                {{ __('employees.bookings_tab_title') }}
                            </h4>
                            <span class="text-muted">
                                {{ __('employees.bookings_tab_hint') }}
                            </span>
                        </div>
                    </div>

                    {{-- هنا لاحقاً نضع جدول/خرائط حجوزات الموظف --}}
                </div>

            </div>

        </div>
    </div>

@endsection

@push('custom-script')
    <script>
        (function () {
            // -------------------------------
            // 🗺️ Google Maps لعرض Polygon فقط
            // -------------------------------
            const polygonData = @json($workAreaPolygon);

            window.initEmployeeShowMap = function () {
                const mapEl = document.getElementById('employee_work_area_map_show');
                if (!mapEl) return;

                // مركز افتراضي لو ما في Polygon (غزة تقريبياً)
                let center = {lat: 31.5, lng: 34.47};
                let zoom = 11;

                const map = new google.maps.Map(mapEl, {
                    center,
                    zoom,
                });

                if (Array.isArray(polygonData) && polygonData.length >= 3) {
                    const path = polygonData.map(p => ({lat: Number(p.lat), lng: Number(p.lng)}));

                    const polygon = new google.maps.Polygon({
                        paths: path,
                        strokeColor: '#0d6efd',
                        strokeOpacity: 0.9,
                        strokeWeight: 2,
                        fillColor: '#0d6efd',
                        fillOpacity: 0.20,
                        clickable: false,
                    });

                    polygon.setMap(map);

                    // اضبط الـ bounds حتى تظهر المنطقة كاملة
                    const bounds = new google.maps.LatLngBounds();
                    path.forEach(p => bounds.extend(p));
                    map.fitBounds(bounds);
                }
            };
        })();
    </script>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initEmployeeShowMap"
        async
        defer>
    </script>
@endpush