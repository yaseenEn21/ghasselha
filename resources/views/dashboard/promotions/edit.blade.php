{{-- edit promotions blade --}}

@extends('base.layout.app')

@section('content')

@section('top-btns')
    <div>
        <a href="{{ route('dashboard.promotions.show', $promotion) }}" class="btn btn-light-primary">
            {{ __('promotions.view') }}
        </a>
        <a href="{{ route('dashboard.promotions.index') }}" class="btn btn-light">
            {{ __('promotions.back_to_list') }}
        </a>
    </div>
@endsection

@php
    $nameAr = old('name.ar', data_get($promotion->name, 'ar', ''));
    $nameEn = old('name.en', data_get($promotion->name, 'en', ''));

    $descAr = old('description.ar', data_get($promotion->description, 'ar', ''));
    $descEn = old('description.en', data_get($promotion->description, 'en', ''));

    $startsAt = old('starts_at', optional($promotion->starts_at)->format('Y-m-d'));
    $endsAt = old('ends_at', optional($promotion->ends_at)->format('Y-m-d'));

    $isActive = (int) old('is_active', $promotion->is_active ? 1 : 0);
@endphp

<div class="card">
    <form id="promotion_form" action="{{ route('dashboard.promotions.update', $promotion) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                <div class="col-lg-8">

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">{{ __('promotions.basic_data') }}</span>
                                <span
                                    class="text-muted mt-1 fw-semibold fs-7">{{ __('promotions.basic_data_hint') }}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">{{ __('promotions.name_ar') }}</label>
                                    <input type="text" name="name[ar]" class="form-control"
                                        value="{{ $nameAr }}" placeholder="مثال: خصم الشتاء">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('promotions.name_en') }}</label>
                                    <input type="text" name="name[en]" class="form-control"
                                        value="{{ $nameEn }}" placeholder="Example: Winter Discount">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('promotions.description_ar') }}</label>
                                    <textarea name="description[ar]" class="form-control" rows="3" placeholder="وصف مختصر">{{ $descAr }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('promotions.description_en') }}</label>
                                    <textarea name="description[en]" class="form-control" rows="3" placeholder="Short description">{{ $descEn }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title fw-bold fs-3">{{ __('promotions.period_block') }}</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('promotions.starts_at') }}</label>
                                    <input type="date" name="starts_at" class="form-control"
                                        value="{{ $startsAt }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('promotions.ends_at') }}</label>
                                    <input type="date" name="ends_at" class="form-control"
                                        value="{{ $endsAt }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="card">
                        <div class="card-body">
                            <label class="fw-semibold fs-6 mb-2 d-block">{{ __('promotions.status') }}</label>

                            {{-- مهم جداً: عشان لو طفيت السويتش يوصل 0 --}}
                            <input type="hidden" name="is_active" value="0">

                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    {{ $isActive ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">{{ __('promotions.active') }}</label>
                            </div>

                            <div class="invalid-feedback d-block"></div>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    (function() {
        const isAr = document.documentElement.lang === 'ar';

        // (لو عندك حقول سكوپ/خدمات/باقات في نفس الصفحة أو لاحقاً)
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
            const $sel = $('#applies_to_select');
            if (!$sel.length) return;

            const v = $sel.val();
            $('#services_scope').toggle(v === 'service' || v === 'both');
            $('#packages_scope').toggle(v === 'package' || v === 'both');
        }

        function toggleAllSwitches() {
            if ($('#service_ids').length) {
                $('#service_ids').prop('disabled', $('#apply_all_services').is(':checked')).trigger(
                    'change.select2');
            }
            if ($('#package_ids').length) {
                $('#package_ids').prop('disabled', $('#apply_all_packages').is(':checked')).trigger(
                    'change.select2');
            }
        }

        $('#applies_to_select').on('change', toggleScope);
        $('#apply_all_services, #apply_all_packages').on('change', toggleAllSwitches);

        toggleScope();
        toggleAllSwitches();

        const $form = $('#promotion_form');

        $form.on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            if (window.KH && typeof window.KH.setFormLoading === 'function') {
                window.KH.setFormLoading($form, true, {
                    text: isAr ? 'جاري الحفظ...' : 'Saving...'
                });
            }

            $.ajax({
                url: $form.attr('action'),
                type: 'POST', // مع FormData ووجود _method=PUT داخل الفورم
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('promotions.done') }}",
                        text: res.message ||
                            "{{ __('promotions.updated_successfully') }}",
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

    })();
</script>
@endpush
