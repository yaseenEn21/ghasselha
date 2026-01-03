@extends('base.layout.app')

@section('title', __('employees.edit'))

@push('custom-style')
    <style>
        #employee_work_area_map {
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
        <a href="{{ route('dashboard.employees.index') }}" class="btn btn-light">
            {{ __('employees.title') }}
        </a>
        <a href="{{ route('dashboard.employees.show', $employee->id) }}" class="btn btn-secondary ms-2">
            {{ __('employees.singular') }}
        </a>
    @endsection

    <form id="employee_edit_form" action="{{ route('dashboard.employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-6">

            {{-- 👤 معلومات المستخدم الأساسية --}}
            <div class="col-xl-8">
                <div class="card card-flush h-100">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">
                                {{ __('employees.edit') }}
                            </span>
                            <span class="text-muted mt-1 fw-semibold fs-7">
                                {{ __('employees.biker_type_label') }}
                            </span>
                        </h3>
                    </div>

                    <div class="card-body pt-0">

                        <div id="form_result" class="alert d-none mb-6"></div>

                        <div class="row g-6">

                            {{-- الاسم --}}
                            <div class="col-md-6 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.name') }}
                                </label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('name', $employee->user->name) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- الجوال --}}
                            <div class="col-md-6 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.mobile') }}
                                </label>
                                <input type="text" name="mobile" class="form-control"
                                       value="{{ old('mobile', $employee->user->mobile) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- الإيميل --}}
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.email') }}
                                </label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $employee->user->email) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- كلمة المرور (اختياري) --}}
                            <div class="col-md-3 fv-row">
                                <label class="fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.password') }}
                                    <span class="text-muted fs-8">
                                        ({{ app()->getLocale() === 'ar' ? 'اتركه فارغاً لعدم التغيير' : 'leave blank to keep current' }})
                                    </span>
                                </label>
                                <input type="password" name="password" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- تأكيد كلمة المرور --}}
                            <div class="col-md-3 fv-row">
                                <label class="fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.password_confirmation') }}
                                </label>
                                <input type="password" name="password_confirmation" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- تاريخ الميلاد --}}
                            <div class="col-md-4 fv-row">
                                <label class="fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.birth_date') }}
                                </label>
                                <input type="date" name="birth_date" class="form-control"
                                       value="{{ old('birth_date', optional($employee->user->birth_date)->format('Y-m-d')) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- الجنس --}}
                            <div class="col-md-4 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">
                                    {{ __('employees.fields.gender') }}
                                </label>
                                <select name="gender" class="form-select">
                                    <option value="male"
                                        {{ old('gender', $employee->user->gender) === 'male' ? 'selected' : '' }}>
                                        {{ __('employees.fields.gender_male') }}
                                    </option>
                                    <option value="female"
                                        {{ old('gender', $employee->user->gender) === 'female' ? 'selected' : '' }}>
                                        {{ __('employees.fields.gender_female') }}
                                    </option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            {{-- ⚙️ إعدادات الحساب --}}
            <div class="col-xl-4">
                <div class="card card-flush h-100">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-4 mb-1">
                                {{ __('employees.singular_title') ?? __('employees.singular') }}
                            </span>
                            <span class="text-muted mt-1 fw-semibold fs-7">
                                {{ __('employees.biker_type_label') }}
                            </span>
                        </h3>
                    </div>

                    <div class="card-body pt-0">

                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active_switch"
                                   name="is_active"
                                   {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active_switch">
                                {{ __('employees.fields.is_active') }}
                            </label>
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" value="1" id="notification_switch"
                                   name="notification"
                                   {{ old('notification', $employee->user->notification) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="notification_switch">
                                {{ __('employees.fields.notification') }}
                            </label>
                        </div>

                        <div class="text-muted fs-7">
                            {{ __('employees.biker_type_label') }}
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- 🕒 ساعات العمل الأسبوعية --}}
        <div class="card mt-6">
            <div class="card-header border-0 pt-5">
                <div class="d-flex justify-content-between align-items-start w-100">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'ساعات العمل الأسبوعية' : 'Weekly working hours' }}
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">
                            {{ app()->getLocale() === 'ar'
                                ? 'حدّد فترة العمل الأساسية وفترة الاستراحة (اختياري) لكل يوم.'
                                : 'Define main working interval and (optional) break interval for each day.' }}
                        </span>
                    </h3>

                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" id="btn_copy_first_day">
                            <i class="ki-duotone ki-copy fs-3 me-2"></i>
                            {{ app()->getLocale() === 'ar' ? 'نسخ أول يوم لباقي الأيام' : 'Copy first day to all' }}
                        </button>
                    </div>
                </div>
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

                <table class="table align-middle table-row-dashed weekly-work-hours-table">
                    <thead>
                        <tr class="fw-semibold fs-7 text-muted">
                            <th>{{ app()->getLocale() === 'ar' ? 'اليوم' : 'Day' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'بداية الدوام' : 'Work start' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'نهاية الدوام' : 'Work end' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'بداية الاستراحة' : 'Break start' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'نهاية الاستراحة' : 'Break end' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'استراحة فعّالة' : 'Break active' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daysLabels as $dayKey => $dayLabel)
                            @php
                                $work  = $weeklyByDay[$dayKey]['work']  ?? null;
                                $break = $weeklyByDay[$dayKey]['break'] ?? null;
                            @endphp
                            <tr data-day="{{ $dayKey }}">
                                <td class="fw-bold">{{ $dayLabel }}</td>

                                <td>
                                    <input type="time" name="work[{{ $dayKey }}][start_time]"
                                           class="form-control form-control-sm"
                                           value="{{ old("work.$dayKey.start_time", $work['start_time'] ?? '') }}">
                                </td>

                                <td>
                                    <input type="time" name="work[{{ $dayKey }}][end_time]"
                                           class="form-control form-control-sm"
                                           value="{{ old("work.$dayKey.end_time", $work['end_time'] ?? '') }}">
                                </td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" value="1"
                                               name="work[{{ $dayKey }}][is_active]"
                                            {{ old("work.$dayKey.is_active", $work['is_active'] ?? false) ? 'checked' : '' }}>
                                    </div>
                                </td>

                                <td>
                                    <input type="time" name="break[{{ $dayKey }}][start_time]"
                                           class="form-control form-control-sm"
                                           value="{{ old("break.$dayKey.start_time", $break['start_time'] ?? '') }}">
                                </td>

                                <td>
                                    <input type="time" name="break[{{ $dayKey }}][end_time]"
                                           class="form-control form-control-sm"
                                           value="{{ old("break.$dayKey.end_time", $break['end_time'] ?? '') }}">
                                </td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" value="1"
                                               name="break[{{ $dayKey }}][is_active]"
                                            {{ old("break.$dayKey.is_active", $break['is_active'] ?? false) ? 'checked' : '' }}>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-muted fs-7 mt-3">
                    {{ app()->getLocale() === 'ar'
                        ? 'يمكنك ترك الحقول فارغة لليوم الذي لا يعمل فيه الموظف.'
                        : 'You can leave fields empty for days when the employee does not work.' }}
                </div>

            </div>
        </div>

        {{-- 🧼 الخدمات --}}
        <div class="card mt-6">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-4 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'الخدمات التي ينفذها الموظف' : 'Services handled by employee' }}
                    </span>
                    <span class="text-muted mt-1 fw-semibold fs-7">
                        {{ app()->getLocale() === 'ar'
                            ? 'اختر الخدمات التي يمكن للموظف تنفيذها.'
                            : 'Select which services this employee can perform.' }}
                    </span>
                </h3>
            </div>
            <div class="card-body pt-0">

                <select name="services[]" class="form-select" data-control="select2"
                        data-placeholder="{{ app()->getLocale() === 'ar' ? 'اختر الخدمات' : 'Select services' }}" multiple>
                    @php $locale = app()->getLocale(); @endphp
                    @foreach($services as $service)
                        @php
                            $sName =
                                $service->name[$locale] ??
                                (is_array($service->name) ? (reset($service->name) ?: '') : $service->name);
                        @endphp
                        <option value="{{ $service->id }}"
                            {{ in_array($service->id, old('services', $selectedServiceIds ?? [])) ? 'selected' : '' }}>
                            {{ $sName }}
                        </option>
                    @endforeach
                </select>

                <div class="text-muted fs-7 mt-3">
                    {{ app()->getLocale() === 'ar'
                        ? 'يمكنك تعديل الخدمات في أي وقت.'
                        : 'You can modify services at any time.' }}
                </div>

            </div>
        </div>

        {{-- 🗺️ منطقة تغطية العمل --}}
        <div class="card mt-6">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-4 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'منطقة تغطية العمل' : 'Work coverage area' }}
                    </span>
                    <span class="text-muted mt-1 fw-semibold fs-7">
                        {{ app()->getLocale() === 'ar'
                            ? 'ارسم مضلع (Polygon) يحدد منطقة عمل الموظف، أو عدّل المضلع الحالي.'
                            : 'Draw a polygon defining the employee’s work area, or adjust the existing one.' }}
                    </span>
                </h3>
            </div>
            <div class="card-body pt-0">

                <input type="hidden" name="work_area_polygon" id="work_area_polygon"
                       value="{{ old('work_area_polygon', $workAreaPolygonJson) }}">

                <div id="employee_work_area_map" class="mb-3"></div>

                <button type="button" class="btn btn-sm btn-light-danger mb-2" id="btn_clear_polygon">
                    {{ app()->getLocale() === 'ar' ? 'مسح المضلع' : 'Clear polygon' }}
                </button>

                <div class="text-muted fs-7">
                    {{ app()->getLocale() === 'ar'
                        ? 'استخدم أداة المضلع من شريط الأدوات في أعلى الخريطة لرسم أو تعديل منطقة التغطية.'
                        : 'Use the polygon tool from the toolbar at the top of the map to draw or edit the coverage area.' }}
                </div>

            </div>
        </div>

        {{-- أزرار الحفظ --}}
        <div class="d-flex justify-content-end mt-6">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('employees.edit') }}</span>
            </button>
        </div>
    </form>
@endsection

@push('custom-script')
<script>
    (function () {
        const $form = $('#employee_edit_form');

        // ✅ إرسال الفورم بالـ AJAX
        $form.on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            if (window.KH && typeof window.KH.setFormLoading === 'function') {
                window.KH.setFormLoading($form, true, {
                    text: '{{ app()->getLocale() === 'ar' ? 'جاري الحفظ...' : 'Saving...' }}'
                });
            }

            $.ajax({
                url: $form.attr('action'),
                type: 'POST', // Laravel سيستخدم _method=PUT
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ app()->getLocale() === 'ar' ? 'تم' : 'Done' }}',
                        text: res.message || '{{ __('employees.updated_successfully') }}',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (res.redirect) {
                        window.location.href = res.redirect;
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                            window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                                globalAlertSelector: '#form_result'
                            });
                        }
                    } else {
                        let msg = '{{ app()->getLocale() === 'ar' ? 'حدث خطأ غير متوقع.' : 'Unexpected error occurred.' }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire(
                            '{{ app()->getLocale() === 'ar' ? 'خطأ' : 'Error' }}',
                            msg,
                            'error'
                        );
                    }
                },
                complete: function () {
                    if (window.KH && typeof window.KH.setFormLoading === 'function') {
                        window.KH.setFormLoading($form, false);
                    }
                }
            });
        });

        // 🗺️ Google Maps + Drawing Manager
        let map, drawingManager, currentPolygon = null;

        window.initEmployeeEditMap = function () {
            const mapEl = document.getElementById('employee_work_area_map');
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

            google.maps.event.addListener(
                drawingManager,
                'overlaycomplete',
                function (e) {
                    if (e.type === google.maps.drawing.OverlayType.POLYGON) {
                        if (currentPolygon) {
                            currentPolygon.setMap(null);
                        }
                        currentPolygon = e.overlay;

                        drawingManager.setDrawingMode(null);

                        attachPolygonListeners(currentPolygon);
                        savePolygonToInput();
                    }
                }
            );

            const clearBtn = document.getElementById('btn_clear_polygon');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    if (currentPolygon) {
                        currentPolygon.setMap(null);
                        currentPolygon = null;
                    }
                    document.getElementById('work_area_polygon').value = '';
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
            const hidden = document.getElementById('work_area_polygon');
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
            const hidden = document.getElementById('work_area_polygon');
            if (!hidden || !hidden.value) return;

            try {
                const coords = JSON.parse(hidden.value);
                if (!Array.isArray(coords) || !coords.length) return;

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

        // 🕒 Copy first day to all days
        const copyBtn = document.getElementById('btn_copy_first_day');

        function getInput(group, dayKey, field) {
            return document.querySelector(`input[name="${group}[${dayKey}][${field}]"]`);
        }

        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const rows = document.querySelectorAll('.weekly-work-hours-table tbody tr[data-day]');
                if (!rows || rows.length < 2) return;

                const sourceDay = rows[0].dataset.day;

                const srcWorkStart   = getInput('work', sourceDay, 'start_time')?.value ?? '';
                const srcWorkEnd     = getInput('work', sourceDay, 'end_time')?.value ?? '';
                const srcWorkActive  = !!getInput('work', sourceDay, 'is_active')?.checked;

                const srcBreakStart  = getInput('break', sourceDay, 'start_time')?.value ?? '';
                const srcBreakEnd    = getInput('break', sourceDay, 'end_time')?.value ?? '';
                const srcBreakActive = !!getInput('break', sourceDay, 'is_active')?.checked;

                rows.forEach((row, idx) => {
                    if (idx === 0) return;

                    const dayKey = row.dataset.day;

                    const wStart = getInput('work', dayKey, 'start_time');
                    const wEnd   = getInput('work', dayKey, 'end_time');
                    const wAct   = getInput('work', dayKey, 'is_active');

                    if (wStart) wStart.value = srcWorkActive ? srcWorkStart : '';
                    if (wEnd)   wEnd.value   = srcWorkActive ? srcWorkEnd   : '';
                    if (wAct)   wAct.checked = srcWorkActive;

                    const bStart = getInput('break', dayKey, 'start_time');
                    const bEnd   = getInput('break', dayKey, 'end_time');
                    const bAct   = getInput('break', dayKey, 'is_active');

                    if (bStart) bStart.value = srcBreakActive ? srcBreakStart : '';
                    if (bEnd)   bEnd.value   = srcBreakActive ? srcBreakEnd   : '';
                    if (bAct)   bAct.checked = srcBreakActive;
                });

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ app()->getLocale() === 'ar' ? 'تم النسخ' : 'Copied' }}',
                        text: '{{ app()->getLocale() === 'ar' ? 'تم تطبيق بيانات أول يوم على جميع الأيام.' : 'First day values applied to all days.' }}',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    })();
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=drawing&callback=initEmployeeEditMap"
    async defer></script>
@endpush
