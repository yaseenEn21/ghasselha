@extends('base.layout.app')

@section('title', __('bookings.create.title'))

@push('custom-style')
<style>
    #addr_pick_map { width:100%; height: 280px; border-radius: .75rem; border:1px solid #E4E6EF; overflow:hidden; }
    .slot-grid { display:flex; flex-wrap:wrap; gap:.5rem; }
    .slot-btn { border:1px dashed #E4E6EF; border-radius:.75rem; padding:.5rem .75rem; cursor:pointer; }
    .slot-btn.active { border-style:solid; }
</style>
@endpush

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.bookings.index') }}" class="btn btn-light">{{ __('bookings.back_to_list') }}</a>
@endsection

<div class="card">
    <form id="booking_form" action="{{ route('dashboard.bookings.store') }}" method="POST">
        @csrf

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-line-tabs mb-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_customer">
                        <i class="ki-duotone ki-profile-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        {{ __('bookings.tabs.customer') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_booking">
                        <i class="ki-duotone ki-calendar fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                        {{ __('bookings.tabs.booking') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_products">
                        <i class="ki-duotone ki-basket fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                        {{ __('bookings.tabs.products') }}
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- =========================
                     TAB 1: Customer + Car + Address
                ========================== --}}
                <div class="tab-pane fade show active" id="tab_customer" role="tabpanel">
                    <div class="row g-8">

                        <div class="col-lg-6">
                            <div class="card card-flush h-100">
                                <div class="card-header pt-6">
                                    <div class="card-title">
                                        <h3 class="fw-bold mb-0">{{ __('bookings.customer.title') }}</h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modal_customer">
                                            <i class="ki-duotone ki-plus fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            {{ __('bookings.customer.add_new') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.fields.user') }}</label>
                                    <select id="user_id" name="user_id" class="form-select" data-placeholder="{{ __('bookings.placeholders.user') }}">
                                        <option></option>
                                    </select>
                                    <div class="invalid-feedback"></div>

                                    <div class="separator my-6"></div>

                                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4">
                                        <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        <div class="d-flex flex-stack flex-grow-1">
                                            <div class="fw-semibold">
                                                <div class="fs-6 text-gray-700">
                                                    {{ __('bookings.customer.notice') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card card-flush h-100">
                                <div class="card-header pt-6">
                                    <div class="card-title">
                                        <h3 class="fw-bold mb-0">{{ __('bookings.car.title') }}</h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <button type="button" class="btn btn-sm btn-light-primary" id="btn_add_car" disabled
                                                data-bs-toggle="modal" data-bs-target="#modal_car">
                                            <i class="ki-duotone ki-plus fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            {{ __('bookings.car.add') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.fields.car') }}</label>
                                    <select id="car_id" name="car_id" class="form-select" data-control="select2"
                                            data-placeholder="{{ __('bookings.placeholders.car') }}" disabled>
                                        <option></option>
                                    </select>
                                    <div class="invalid-feedback"></div>

                                    <div class="separator my-6"></div>

                                    <h4 class="fw-bold mb-3">{{ __('bookings.address.title') }}</h4>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="required fw-semibold fs-6 mb-0">{{ __('bookings.fields.address') }}</label>
                                        <button type="button" class="btn btn-sm btn-light-primary" id="btn_add_address" disabled
                                                data-bs-toggle="modal" data-bs-target="#modal_address">
                                            <i class="ki-duotone ki-plus fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            {{ __('bookings.address.add') }}
                                        </button>
                                    </div>

                                    <select id="address_id" name="address_id" class="form-select" data-control="select2"
                                            data-placeholder="{{ __('bookings.placeholders.address') }}" disabled>
                                        <option></option>
                                    </select>
                                    <div class="invalid-feedback"></div>

                                    <div class="form-text mt-2">
                                        {{ __('bookings.address.hint') }}
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- =========================
                     TAB 2: Booking Details + Slots
                ========================== --}}
                <div class="tab-pane fade" id="tab_booking" role="tabpanel">
                    <div class="row g-8">

                        <div class="col-lg-8">
                            <div class="card card-flush">
                                <div class="card-header pt-6">
                                    <div class="card-title">
                                        <h3 class="fw-bold mb-0">{{ __('bookings.details.title') }}</h3>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row g-6">

                                        <div class="col-md-6">
                                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.fields.service') }}</label>
                                            <select id="service_id" name="service_id" class="form-select" data-control="select2"
                                                    data-placeholder="{{ __('bookings.placeholders.service') }}">
                                                <option></option>
                                                @foreach($services as $srv)
                                                    <option value="{{ $srv->id }}"
                                                            data-duration="{{ (int)$srv->duration_minutes }}">
                                                        {{ function_exists('i18n') ? i18n($srv->name) : ($srv->name['ar'] ?? $srv->name['en'] ?? '') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.fields.date') }}</label>
                                            <input type="date" id="booking_date" name="booking_date" class="form-control"
                                                   value="{{ old('booking_date') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.fields.employee') }}</label>
                                            <select id="employee_id" name="employee_id" class="form-select" disabled>
                                                <option value="">{{ __('bookings.placeholders.employee_auto') }}</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="col-12">
                                            <div class="separator my-2"></div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.fields.package_subscription') }}</label>
                                            <select id="package_subscription_id" name="package_subscription_id" class="form-select" disabled>
                                                <option value="">{{ __('bookings.placeholders.package_optional') }}</option>
                                            </select>
                                            <div class="form-text">
                                                {{ __('bookings.package.hint') }}
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.fields.note') }}</label>
                                            <input type="text" name="note" class="form-control" placeholder="{{ __('bookings.placeholders.note') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="col-12">
                                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.fields.time') }}</label>

                                            <input type="hidden" name="start_time" id="start_time">

                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <button type="button" class="btn btn-light-primary btn-sm" id="btn_load_slots" disabled>
                                                    <i class="ki-duotone ki-reload fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                    {{ __('bookings.slots.load') }}
                                                </button>

                                                <span class="text-muted fs-7" id="slots_meta"></span>
                                            </div>

                                            <div id="slots_wrap" class="slot-grid"></div>

                                            <div class="text-danger mt-2" id="slots_error"></div>
                                            <div class="form-text mt-2">{{ __('bookings.slots.hint') }}</div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card card-flush">
                                <div class="card-header pt-6">
                                    <div class="card-title">
                                        <h3 class="fw-bold mb-0">{{ __('bookings.summary.title') }}</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column gap-4">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">{{ __('bookings.summary.duration') }}</span>
                                            <span class="fw-bold" id="sum_duration">—</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">{{ __('bookings.summary.start') }}</span>
                                            <span class="fw-bold" id="sum_start">—</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">{{ __('bookings.summary.end') }}</span>
                                            <span class="fw-bold" id="sum_end">—</span>
                                        </div>

                                        <div class="separator"></div>

                                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4">
                                            <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                            <div class="fw-semibold">
                                                <div class="fs-6 text-gray-700">
                                                    {{ __('bookings.summary.notice') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- =========================
                     TAB 3: Products
                ========================== --}}
                <div class="tab-pane fade" id="tab_products" role="tabpanel">
                    <div class="card card-flush">
                        <div class="card-header pt-6">
                            <div class="card-title">
                                <h3 class="fw-bold mb-0">{{ __('bookings.products.title') }}</h3>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-sm btn-light-primary" id="btn_add_product_row">
                                    <i class="ki-duotone ki-plus fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    {{ __('bookings.products.add_row') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed">
                                    <thead>
                                    <tr class="text-muted fw-bold text-uppercase fs-7">
                                        <th style="width:55%">{{ __('bookings.products.product') }}</th>
                                        <th style="width:25%">{{ __('bookings.products.qty') }}</th>
                                        <th class="text-end" style="width:20%">{{ __('bookings.products.actions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="products_tbody"></tbody>
                                </table>
                            </div>

                            <div class="form-text">
                                {{ __('bookings.products.hint') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /tab-content --}}

        </div>

        <div class="card-footer d-flex justify-content-end gap-3">
            <button type="button" class="btn btn-light" onclick="history.back()">{{ __('bookings.cancel') }}</button>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('bookings.save') }}</span>
            </button>
        </div>
    </form>
</div>


{{-- =========================
    MODAL: Add Customer
========================= --}}
<div class="modal fade" id="modal_customer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold">{{ __('bookings.customer.modal_title') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="customer_form">
                    <div class="alert d-none" id="customer_result"></div>

                    <div class="row g-6">
                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.customer.name') }}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{ __('bookings.customer.name_ph') }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.customer.phone') }}</label>
                            <input type="text" name="mobile" class="form-control" placeholder="{{ __('bookings.customer.phone_ph') }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-8">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('bookings.close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('bookings.create.title') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- =========================
    MODAL: Add Car
========================= --}}
<div class="modal fade" id="modal_car" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold">{{ __('bookings.car.modal_title') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="car_form">
                    <div class="alert d-none" id="car_result"></div>

                    <div class="row g-6">
                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.car.make') }}</label>
                            <select id="vehicle_make_id" name="vehicle_make_id" class="form-select" data-control="select2"
                                    data-placeholder="{{ __('bookings.car.make_ph') }}">
                                <option></option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.car.model') }}</label>
                            <select id="vehicle_model_id" name="vehicle_model_id" class="form-select" data-control="select2"
                                    data-placeholder="{{ __('bookings.car.model_ph') }}">
                                <option></option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.car.plate_number') }}</label>
                            <input type="text" name="plate_number" class="form-control" placeholder="1234">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.car.plate_letters') }}</label>
                            <input type="text" name="plate_letters" class="form-control" placeholder="ABC">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.car.plate_letters_ar') }}</label>
                            <input type="text" name="plate_letters_ar" class="form-control" placeholder="أ ب ج">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.car.color') }}</label>
                            <select name="color" class="form-select">
                                <option value="">{{ __('bookings.optional') }}</option>
                                @foreach(['red','silver','white','black','brown','orange','purple','gold','green','blue','yellow','beige'] as $c)
                                    <option value="{{ $c }}">{{ __('bookings.colors.'.$c) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label class="fw-semibold fs-6 mb-2 d-block">{{ __('bookings.car.default') }}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1">
                                <label class="form-check-label">{{ __('bookings.yes') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-8">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('bookings.close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('bookings.create.title') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- =========================
    MODAL: Add Address
========================= --}}
<div class="modal fade" id="modal_address" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold">{{ __('bookings.address.modal_title') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="address_form">
                    <div class="alert d-none" id="address_result"></div>

                    <div class="row g-6">
                        <div class="col-md-3 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('bookings.address.type') }}</label>
                            <select name="type" class="form-select">
                                <option value="home">{{ __('bookings.address.types.home') }}</option>
                                <option value="work">{{ __('bookings.address.types.work') }}</option>
                                <option value="other">{{ __('bookings.address.types.other') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.address.city') }}</label>
                            <input name="city" class="form-control" placeholder="{{ __('bookings.optional') }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.address.area') }}</label>
                            <input name="area" class="form-control" placeholder="{{ __('bookings.optional') }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.address.country') }}</label>
                            <input name="country" class="form-control" placeholder="{{ __('bookings.optional') }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-8 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.address.address_line') }}</label>
                            <input name="address_line" class="form-control" placeholder="{{ __('bookings.address.address_line_ph') }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.address.landmark') }}</label>
                            <input name="landmark" class="form-control" placeholder="{{ __('bookings.optional') }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">Lat</label>
                            <input name="lat" id="addr_lat" class="form-control" placeholder="26.35">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">Lng</label>
                            <input name="lng" id="addr_lng" class="form-control" placeholder="50.08">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <div id="addr_pick_map" class="mb-2"></div>
                            <div class="form-text">{{ __('bookings.address.map_hint') }}</div>
                        </div>

                        <div class="col-md-8 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('bookings.address.link') }}</label>
                            <input name="address_link" class="form-control" placeholder="https://maps.google.com/...">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="fw-semibold fs-6 mb-2 d-block">{{ __('bookings.address.default') }}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1">
                                <label class="form-check-label">{{ __('bookings.yes') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-8">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('bookings.close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('bookings.create.title') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection


@push('custom-script')
<script>
(function () {
    const isAr = document.documentElement.lang === 'ar';

    const $form = $('#booking_form');

    // ----------------------------
    // Select2: user (ajax)
    // ----------------------------
    const $user = $('#user_id');

    if ($user.data('select2')) {
        $user.select2('destroy');
    }

    $user.select2({
        width: '100%',
        placeholder: $user.data('placeholder') || '',
        allowClear: true,
        minimumInputLength: 0, // ✅ يجيب عند فتح القائمة
        ajax: {
            url: "{{ route('dashboard.bookings.lookups.users') }}",
            dataType: 'json',
            delay: 250,
            cache: true,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({ results: data.results || [] }),
            error: xhr => console.log('users lookup error:', xhr.status, xhr.responseText),
        }
    });

    // service select2 (static)
    $('#service_id').select2({ allowClear: true });

    // car/address select2 (static but dynamic items)
    $('#car_id').select2({ allowClear: true });
    $('#address_id').select2({ allowClear: true });

    // Enable buttons when user selected
    function setUserDependentEnabled(enabled) {
        $('#car_id').prop('disabled', !enabled);
        $('#address_id').prop('disabled', !enabled);
        $('#package_subscription_id').prop('disabled', !enabled);

        $('#btn_add_car').prop('disabled', !enabled);
        $('#btn_add_address').prop('disabled', !enabled);
    }

    setUserDependentEnabled(false);

    $('#user_id').on('change', function () {
        
        console.log('User changed');
        
        const userId = $(this).val();

        // reset dependents
        $('#car_id').empty().trigger('change');
        $('#address_id').empty().trigger('change');
        $('#package_subscription_id').empty().append(`<option value="">{{ __('bookings.placeholders.package_optional') }}</option>`);

        $('#start_time').val('');
        $('#slots_wrap').empty();
        $('#employee_id').prop('disabled', true).empty().append(`<option value="">{{ __('bookings.placeholders.employee_auto') }}</option>`);
        $('#btn_load_slots').prop('disabled', true);

        if (!userId) {
            setUserDependentEnabled(false);
            return;
        }

        setUserDependentEnabled(true);

        // load cars
        $.get("{{ url('dashboard/bookings/lookups/users') }}/" + userId + "/cars")
            .done(res => {
                const items = res.items || [];
                $('#car_id').empty().append('<option></option>');
                items.forEach(it => $('#car_id').append(new Option(it.text, it.id, false, false)));
                $('#car_id').trigger('change');
            });

        // load addresses
        $.get("{{ url('dashboard/bookings/lookups/users') }}/" + userId + "/addresses")
            .done(res => {
                const items = res.items || [];
                $('#address_id').empty().append('<option></option>');
                items.forEach(it => {
                    const opt = new Option(it.text, it.id, false, false);
                    $(opt).attr('data-lat', it.lat).attr('data-lng', it.lng);
                    $('#address_id').append(opt);
                });
                $('#address_id').trigger('change');
            });

        // (اختياري) package subscriptions لو عندك endpoint لاحقًا
    });

    // ----------------------------
    // slots loading
    // ----------------------------
    let slotsCache = []; // items from server

    function canLoadSlots() {
        return !!$('#service_id').val() && !!$('#address_id').val() && !!$('#booking_date').val();
    }

    function toggleSlotsButton() {
        $('#btn_load_slots').prop('disabled', !canLoadSlots());
    }

    $('#service_id, #address_id, #booking_date').on('change', function () {
        toggleSlotsButton();

        // reset selection
        $('#start_time').val('');
        $('#slots_wrap').empty();
        $('#slots_error').text('');
        $('#slots_meta').text('');
        $('#employee_id').prop('disabled', true).empty().append(`<option value="">{{ __('bookings.placeholders.employee_auto') }}</option>`);
        $('#sum_start').text('—');
        $('#sum_end').text('—');
        const dur = $('#service_id option:selected').data('duration');
        $('#sum_duration').text(dur ? (dur + ' {{ __("bookings.minutes") }}') : '—');
    });

    function renderSlots(items, meta) {
        const $wrap = $('#slots_wrap');
        $wrap.empty();

        slotsCache = items || [];

        if (!slotsCache.length) {
            const code = meta?.error_code || null;
            let msg = "{{ __('bookings.no_slots') }}";
            if (code === 'OUT_OF_COVERAGE') msg = "{{ __('bookings.out_of_coverage') }}";
            if (code === 'NO_WORKING_HOURS') msg = "{{ __('bookings.no_working_hours') }}";
            $('#slots_error').text(msg);
            return;
        }

        $('#slots_error').text('');

        slotsCache.forEach((s, idx) => {
            const id = 'slot_' + idx;
            const html = `
                <label class="slot-btn" for="${id}" data-idx="${idx}">
                    <input class="d-none" type="radio" name="slot_pick" id="${id}" value="${s.start_time}">
                    <div class="fw-bold">${s.start_time} - ${s.end_time}</div>
                    <div class="text-muted fs-8">${(s.employees?.length || 0)} {{ __('bookings.slots.employees') }}</div>
                </label>
            `;
            $wrap.append(html);
        });
    }

    $('#btn_load_slots').on('click', function () {
        if (!canLoadSlots()) return;

        $('#slots_error').text('');
        $('#slots_meta').text(isAr ? 'جاري تحميل المواعيد...' : 'Loading slots...');

        $.get("{{ route('dashboard.bookings.slots') }}", {
            service_id: $('#service_id').val(),
            address_id: $('#address_id').val(),
            booking_date: $('#booking_date').val()
        })
        .done(res => {
            $('#slots_meta').text('');
            renderSlots(res.items || [], res.meta || {});
        })
        .fail(xhr => {
            $('#slots_meta').text('');
            $('#slots_error').text(xhr.responseJSON?.message || 'Error');
        });
    });

    // choose slot
    $(document).on('click', '.slot-btn', function () {
        $('.slot-btn').removeClass('active');
        $(this).addClass('active');

        const idx = parseInt($(this).data('idx'), 10);
        const s = slotsCache[idx];

        if (!s) return;

        $('#start_time').val(s.start_time);

        // summary
        $('#sum_start').text(s.start_time);
        $('#sum_end').text(s.end_time);

        // employees select
        const employees = s.employees || [];
        const $emp = $('#employee_id');

        $emp.empty().append(`<option value="">{{ __('bookings.placeholders.employee_auto') }}</option>`);

        employees.forEach(e => {
            $emp.append(new Option(e.name, e.employee_id, false, false));
        });

        // enable select only if multiple
        $emp.prop('disabled', employees.length <= 1);

        // if one employee -> set it (optional)
        if (employees.length === 1) {
            $emp.val(employees[0].employee_id);
        } else {
            $emp.val('');
        }
    });

    // ----------------------------
    // products repeater
    // ----------------------------
    function productRowTemplate(index) {
        return `
            <tr data-index="${index}">
                <td>
                    <select class="form-select js-product" name="products[${index}][product_id]" data-placeholder="{{ __('bookings.products.product_ph') }}">
                        <option></option>
                    </select>
                    <div class="invalid-feedback"></div>
                </td>
                <td>
                    <input type="number" min="1" class="form-control" name="products[${index}][qty]" value="1">
                    <div class="invalid-feedback"></div>
                </td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-light-danger js-remove-row">
                        <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </button>
                </td>
            </tr>
        `;
    }

    function initProductSelect2($el) {
        $el.select2({
            ajax: {
                url: "{{ route('dashboard.bookings.lookups.products') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term || '' }),
                processResults: data => data
            },
            allowClear: true,
            minimumInputLength: 1
        });
    }

    let productIndex = 0;

    $('#btn_add_product_row').on('click', function () {
        const html = productRowTemplate(productIndex);
        $('#products_tbody').append(html);
        initProductSelect2($('#products_tbody tr:last .js-product'));
        productIndex++;
    });

    $(document).on('click', '.js-remove-row', function () {
        $(this).closest('tr').remove();
    });

    // ----------------------------
    // AJAX Submit main form
    // ----------------------------
    $form.on('submit', function (e) {
        e.preventDefault();

        if (window.KH && typeof window.KH.setFormLoading === 'function') {
            window.KH.setFormLoading($form, true, { text: isAr ? 'جاري الحفظ...' : 'Saving...' });
        }

        const formData = new FormData($form[0]);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('bookings.done') }}",
                    text: res.message || "{{ __('bookings.created_successfully') }}",
                    timer: 1800,
                    showConfirmButton: false
                });

                if (res.redirect) window.location.href = res.redirect;
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                        window.KH.showValidationErrors($form, xhr.responseJSON.errors, { globalAlertSelector: '#form_result' });
                    } else {
                        $('#form_result').removeClass('d-none alert-success').addClass('alert-danger').text(xhr.responseJSON.message || 'Validation error');
                    }

                    // slot error place
                    if (xhr.responseJSON.errors.start_time) {
                        $('#slots_error').text(xhr.responseJSON.errors.start_time[0]);
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Unexpected error', 'error');
                }
            },
            complete: function () {
                if (window.KH && typeof window.KH.setFormLoading === 'function') {
                    window.KH.setFormLoading($form, false);
                }
            }
        });
    });

    // ----------------------------
    // MODAL: customer
    // ----------------------------
    $('#customer_form').on('submit', function (e) {
        e.preventDefault();

        const $f = $(this);
        const data = $f.serialize();

        // clear
        $f.find('.is-invalid').removeClass('is-invalid');
        $f.find('.invalid-feedback').text('');
        $('#customer_result').addClass('d-none').removeClass('alert-danger alert-success').text('');

        $.post("{{ route('dashboard.bookings.users.store') }}", data)
            .done(res => {
                $('#modal_customer').modal('hide');

                // set select2 value
                const option = new Option(res.text, res.id, true, true);
                $('#user_id').append(option).trigger('change');

                if (window.toastr) toastr.success(res.message || 'Created');
            })
            .fail(xhr => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(k => {
                        const $input = $f.find(`[name="${k}"]`);
                        $input.addClass('is-invalid');
                        $input.closest('.fv-row').find('.invalid-feedback').first().text(xhr.responseJSON.errors[k][0]);
                    });
                } else {
                    $('#customer_result').removeClass('d-none').addClass('alert alert-danger').text(xhr.responseJSON?.message || 'Error');
                }
            });
    });

    // ----------------------------
    // MODAL: car (select2 in modal)
    // ----------------------------
    const carModal = document.getElementById('modal_car');

    $('#vehicle_make_id').select2({
        dropdownParent: $('#modal_car'),
        ajax: {
            url: "{{ route('dashboard.lookups.vehicle_makes') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => data
        },
        allowClear: true,
        minimumInputLength: 0
    });

    $('#vehicle_model_id').select2({
        dropdownParent: $('#modal_car'),
        ajax: {
            url: "{{ route('dashboard.lookups.vehicle_models') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({
                q: params.term || '',
                make_id: $('#vehicle_make_id').val() || ''
            }),
            processResults: data => data
        },
        allowClear: true,
        minimumInputLength: 0
    });

    $('#vehicle_make_id').on('change', function () {
        $('#vehicle_model_id').val(null).trigger('change');
    });

    $('#car_form').on('submit', function (e) {
        e.preventDefault();

        const userId = $('#user_id').val();
        if (!userId) {
            Swal.fire('Error', "{{ __('bookings.select_user_first') }}", 'error');
            return;
        }

        const $f = $(this);
        const data = $f.serialize();

        $f.find('.is-invalid').removeClass('is-invalid');
        $f.find('.invalid-feedback').text('');
        $('#car_result').addClass('d-none').removeClass('alert-danger alert-success').text('');

        $.post("{{ url('dashboard/bookings/users') }}/" + userId + "/cars", data)
            .done(res => {
                $('#modal_car').modal('hide');

                const opt = new Option(res.text, res.id, true, true);
                $('#car_id').append(opt).trigger('change');

                if (window.toastr) toastr.success(res.message || 'Created');
            })
            .fail(xhr => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(k => {
                        const $input = $f.find(`[name="${k}"]`);
                        $input.addClass('is-invalid');
                        $input.closest('.fv-row').find('.invalid-feedback').first().text(xhr.responseJSON.errors[k][0]);
                    });
                } else {
                    $('#car_result').removeClass('d-none').addClass('alert alert-danger').text(xhr.responseJSON?.message || 'Error');
                }
            });
    });

    // ----------------------------
    // MODAL: address + google maps picker
    // ----------------------------
    let addrMap, addrMarker;

    window.initBookingAddressMap = function () {
        const mapEl = document.getElementById('addr_pick_map');
        if (!mapEl) return;

        const initialCenter = { lat: 26.35, lng: 50.08 };

        addrMap = new google.maps.Map(mapEl, {
            center: initialCenter,
            zoom: 12
        });

        addrMarker = new google.maps.Marker({
            position: initialCenter,
            map: addrMap,
            draggable: true
        });

        function setLatLng(latLng) {
            $('#addr_lat').val(latLng.lat().toFixed(7));
            $('#addr_lng').val(latLng.lng().toFixed(7));
        }

        setLatLng(addrMarker.getPosition());

        addrMarker.addListener('dragend', function () {
            setLatLng(addrMarker.getPosition());
        });

        addrMap.addListener('click', function (e) {
            addrMarker.setPosition(e.latLng);
            setLatLng(e.latLng);
        });
    };

    document.getElementById('modal_address')?.addEventListener('shown.bs.modal', function () {
        // init map once
        if (!addrMap && window.google && google.maps) {
            window.initBookingAddressMap();
        } else if (addrMap) {
            google.maps.event.trigger(addrMap, 'resize');
        }
    });

    $('#address_form').on('submit', function (e) {
        e.preventDefault();

        const userId = $('#user_id').val();
        if (!userId) {
            Swal.fire('Error', "{{ __('bookings.select_user_first') }}", 'error');
            return;
        }

        const $f = $(this);
        const data = $f.serialize();

        $f.find('.is-invalid').removeClass('is-invalid');
        $f.find('.invalid-feedback').text('');
        $('#address_result').addClass('d-none').removeClass('alert-danger alert-success').text('');

        $.post("{{ url('dashboard/bookings/users') }}/" + userId + "/addresses", data)
            .done(res => {
                $('#modal_address').modal('hide');

                const opt = new Option(res.text, res.id, true, true);
                $(opt).attr('data-lat', res.lat).attr('data-lng', res.lng);
                $('#address_id').append(opt).trigger('change');

                if (window.toastr) toastr.success(res.message || 'Created');
            })
            .fail(xhr => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(k => {
                        const $input = $f.find(`[name="${k}"]`);
                        $input.addClass('is-invalid');
                        $input.closest('.fv-row').find('.invalid-feedback').first().text(xhr.responseJSON.errors[k][0]);
                    });
                } else {
                    $('#address_result').removeClass('d-none').addClass('alert alert-danger').text(xhr.responseJSON?.message || 'Error');
                }
            });
    });

})();
</script>

{{-- Google Maps (Address picker) --}}
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initBookingAddressMap"
    async defer></script>
@endpush