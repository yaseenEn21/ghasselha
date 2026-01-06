{{-- edit coupons blade --}}

@extends('base.layout.app')

@section('content')

@section('top-btns')
    <div>
        <a href="{{ route('dashboard.promotions.show', $promotion->id) }}" class="btn btn-light-primary">
            {{ __('promotions.view') }}
        </a>
        <a href="{{ route('dashboard.promotions.coupons.index', $promotion->id) }}" class="btn btn-light">
            {{ __('promotions.coupons.back_to_list') }}
        </a>
    </div>
@endsection

@php
    $locale = app()->getLocale();

    $code = old('code', $coupon->code);

    $isActive = (int) old('is_active', $coupon->is_active ? 1 : 0);

    $startsAt = old('starts_at', optional($coupon->starts_at)->format('Y-m-d'));
    $endsAt = old('ends_at', optional($coupon->ends_at)->format('Y-m-d'));

    $discountType = old('discount_type', $coupon->discount_type);
    $discountValue = old('discount_value', $coupon->discount_value);
    $maxDiscount = old('max_discount', $coupon->max_discount);

    $usageLimitTotal = old('usage_limit_total', $coupon->usage_limit_total);
    $usageLimitPerUser = old('usage_limit_per_user', $coupon->usage_limit_per_user);

    $minInvoiceTotal = old('min_invoice_total', $coupon->min_invoice_total);

    $appliesTo = old('applies_to', $coupon->applies_to);

    $applyAllServices = (int) old('apply_all_services', $coupon->apply_all_services ? 1 : 0);
    $applyAllPackages = (int) old('apply_all_packages', $coupon->apply_all_packages ? 1 : 0);

    // علاقات الخدمات/الباقات (يفترض موجودة في الموديل)
    $selectedServices = $coupon->services ?? collect();
    $selectedPackages = $coupon->packages ?? collect();

    $getLocalizedName = function ($model) use ($locale) {
        $arr = $model->name ?? [];
        if (is_array($arr)) {
            return $arr[$locale] ?? (collect($arr)->first() ?? '');
        }
        return '';
    };
@endphp

<div class="card">
    <form id="coupon_edit_form" action="{{ route('dashboard.promotions.coupons.update', [$promotion->id, $coupon->id]) }}"
        method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                <div class="col-lg-8">

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span
                                    class="card-label fw-bold fs-3 mb-1">{{ __('promotions.coupons.basic_data') }}</span>
                            </h3>
                        </div>

                        <div class="card-body pt-0">
                            <div class="row g-6">

                                <div class="col-md-6 fv-row">
                                    <label
                                        class="required fw-semibold fs-6 mb-2">{{ __('promotions.coupons.fields.code') }}</label>
                                    <input type="text" name="code" class="form-control"
                                        value="{{ $code }}" placeholder="EX: NEWYEAR10" maxlength="30" />
                                    <div class="form-text">{{ __('promotions.coupons.code_hint') }}</div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="fw-semibold fs-6 mb-2 d-block">{{ __('promotions.coupons.fields.status') }}</label>

                                    {{-- مهم: عشان لو طفيت السويتش يوصل 0 --}}
                                    <input type="hidden" name="is_active" value="0">

                                    <div
                                        class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-6">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            {{ $isActive ? 'checked' : '' }} />
                                        <label
                                            class="form-check-label fw-semibold">{{ __('promotions.active') }}</label>
                                    </div>
                                    <div class="invalid-feedback d-block"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label
                                        class="fw-semibold fs-6 mb-2">{{ __('promotions.coupons.fields.starts_at') }}</label>
                                    <input type="date" name="starts_at" class="form-control"
                                        value="{{ $startsAt }}" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label
                                        class="fw-semibold fs-6 mb-2">{{ __('promotions.coupons.fields.ends_at') }}</label>
                                    <input type="date" name="ends_at" class="form-control"
                                        value="{{ $endsAt }}" />
                                    <div class="invalid-feedback"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card my-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title fw-bold fs-3">{{ __('promotions.discount_block') }}</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-4 fv-row">
                                    <label
                                        class="required fw-semibold fs-6 mb-2">{{ __('promotions.discount_type') }}</label>
                                    <select name="discount_type" class="form-select">
                                        <option value="percent" {{ $discountType === 'percent' ? 'selected' : '' }}>
                                            {{ __('promotions.discount_type_percent') }}
                                        </option>
                                        <option value="fixed" {{ $discountType === 'fixed' ? 'selected' : '' }}>
                                            {{ __('promotions.discount_type_fixed') }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label
                                        class="required fw-semibold fs-6 mb-2">{{ __('promotions.discount_value') }}</label>
                                    <input type="number" step="0.01" min="0" name="discount_value"
                                        class="form-control" value="{{ $discountValue }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('promotions.max_discount') }}</label>
                                    <input type="number" step="0.01" min="0" name="max_discount"
                                        class="form-control" placeholder="اختياري" value="{{ $maxDiscount }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">{{ __('promotions.coupons.rules') }}</span>
                            </h3>
                        </div>

                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-4 fv-row">
                                    <label
                                        class="fw-semibold fs-6 mb-2">{{ __('promotions.coupons.fields.usage_limit_total') }}</label>
                                    <input type="number" min="1" name="usage_limit_total" class="form-control"
                                        placeholder="—" value="{{ $usageLimitTotal }}" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label
                                        class="fw-semibold fs-6 mb-2">{{ __('promotions.coupons.fields.usage_limit_per_user') }}</label>
                                    <input type="number" min="1" name="usage_limit_per_user"
                                        class="form-control" placeholder="—" value="{{ $usageLimitPerUser }}" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label
                                        class="fw-semibold fs-6 mb-2">{{ __('promotions.coupons.fields.min_invoice_total') }}</label>
                                    <input type="number" step="0.01" min="0" name="min_invoice_total"
                                        class="form-control" placeholder="0.00" value="{{ $minInvoiceTotal }}" />
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="col-lg-4">
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title fw-bold fs-4">{{ __('promotions.scope') }}</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row mb-4">
                                <label
                                    class="required fw-semibold fs-6 mb-2">{{ __('promotions.applies_to') }}</label>
                                <select name="applies_to" id="applies_to_select" class="form-select">
                                    <option value="both" {{ $appliesTo === 'both' ? 'selected' : '' }}>
                                        {{ __('promotions.applies_to_both') }}
                                    </option>
                                    <option value="service" {{ $appliesTo === 'service' ? 'selected' : '' }}>
                                        {{ __('promotions.applies_to_service') }}
                                    </option>
                                    <option value="package" {{ $appliesTo === 'package' ? 'selected' : '' }}>
                                        {{ __('promotions.applies_to_package') }}
                                    </option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div id="services_scope" class="border rounded p-4 mb-4">
                                {{-- مهم: عشان لو طفيت checkbox يوصل 0 --}}
                                <input type="hidden" name="apply_all_services" value="0">

                                <div class="form-check form-switch form-check-custom form-check-solid mb-3">
                                    <input class="form-check-input" type="checkbox" name="apply_all_services"
                                        value="1" id="apply_all_services"
                                        {{ $applyAllServices ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="apply_all_services">
                                        {{ __('promotions.apply_all_services') }}
                                    </label>
                                </div>

                                <label class="fw-semibold fs-6 mb-2">{{ __('promotions.select_services') }}</label>
                                <select name="service_ids[]" id="service_ids" class="form-select" multiple
                                    data-ajax-url="{{ route('dashboard.promotions.search.services') }}">
                                    @foreach ($selectedServices as $s)
                                        <option value="{{ $s->id }}" selected>{{ $getLocalizedName($s) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ __('promotions.select2_hint') }}</div>
                                <div class="invalid-feedback d-block"></div>
                            </div>

                            <div id="packages_scope" class="border rounded p-4">
                                {{-- مهم: عشان لو طفيت checkbox يوصل 0 --}}
                                <input type="hidden" name="apply_all_packages" value="0">

                                <div class="form-check form-switch form-check-custom form-check-solid mb-3">
                                    <input class="form-check-input" type="checkbox" name="apply_all_packages"
                                        value="1" id="apply_all_packages"
                                        {{ $applyAllPackages ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="apply_all_packages">
                                        {{ __('promotions.apply_all_packages') }}
                                    </label>
                                </div>

                                <label class="fw-semibold fs-6 mb-2">{{ __('promotions.select_packages') }}</label>
                                <select name="package_ids[]" id="package_ids" class="form-select" multiple
                                    data-ajax-url="{{ route('dashboard.promotions.search.packages') }}">
                                    @foreach ($selectedPackages as $p)
                                        <option value="{{ $p->id }}" selected>{{ $getLocalizedName($p) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ __('promotions.select2_hint') }}</div>
                                <div class="invalid-feedback d-block"></div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('promotions.save_changes') ?? __('promotions.save') }}</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('custom-script')
<script>
    (function() {

        const isAr = document.documentElement.lang === 'ar';

        function initAjaxSelect2($el) {
            if (!$el || !$el.length) return;

            const url = $el.data('ajax-url');
            $el.select2({
                width: '100%',
                dir: isAr ? 'rtl' : 'ltr',
                placeholder: isAr ? 'اختر...' : 'Select...',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || ''
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

            // open -> fetch first 10 even if empty
            $el.on('select2:open', function() {
                const s2 = $el.data('select2');
                if (!s2) return;
                const $search = $('.select2-container--open .select2-search__field');
                if ($search.length && $search.val() === '') {
                    $search.trigger('input');
                }
            });
        }

        initAjaxSelect2($('#service_ids'));
        initAjaxSelect2($('#package_ids'));

        function toggleScope() {
            const v = $('#applies_to_select').val();
            $('#services_scope').toggle(v === 'service' || v === 'both');
            $('#packages_scope').toggle(v === 'package' || v === 'both');
        }

        function toggleAllSwitches() {
            $('#service_ids').prop('disabled', $('#apply_all_services').is(':checked')).trigger('change.select2');
            $('#package_ids').prop('disabled', $('#apply_all_packages').is(':checked')).trigger('change.select2');
        }

        $('#applies_to_select').on('change', toggleScope);
        $('#apply_all_services, #apply_all_packages').on('change', toggleAllSwitches);

        toggleScope();
        toggleAllSwitches();

        const $form = $('#coupon_edit_form');

        // auto uppercase
        $form.find('input[name="code"]').on('input', function() {
            this.value = (this.value || '').toUpperCase();
        });

        $form.on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            if (window.KH && typeof window.KH.setFormLoading === 'function') {
                window.KH.setFormLoading($form, true, {
                    text: '{{ app()->getLocale() === 'ar' ? 'جاري الحفظ...' : 'Saving...' }}'
                });
            }

            formData.set('_method', 'PUT');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PUT'
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('promotions.done') }}",
                        text: res.message ||
                            "{{ __('promotions.coupons.updated_successfully') }}",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (res.redirect) window.location.href = res.redirect;
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                            window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                                globalAlertSelector: '#form_result'
                            });
                        }
                    } else {
                        let msg =
                            '{{ app()->getLocale() === 'ar' ? 'حدث خطأ غير متوقع.' : 'Unexpected error occurred.' }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                            .message;
                        Swal.fire('{{ app()->getLocale() === 'ar' ? 'خطأ' : 'Error' }}', msg,
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
    })();
</script>
@endpush
