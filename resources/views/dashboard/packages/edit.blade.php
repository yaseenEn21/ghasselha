@extends('base.layout.app')

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.packages.index') }}" class="btn btn-light">
        {{ __('packages.back_to_list') }}
    </a>
@endsection

@php
    $locale = app()->getLocale();
    $imageArUrl = $package->getFirstMediaUrl('image_ar') ?: asset('assets/media/svg/files/blank-image.svg');
    $imageEnUrl = $package->getFirstMediaUrl('image_en') ?: asset('assets/media/svg/files/blank-image.svg');

    $nameAr = old('name.ar', $package->name['ar'] ?? '');
    $nameEn = old('name.en', $package->name['en'] ?? '');
    $descAr = old('description.ar', $package->description['ar'] ?? '');
    $descEn = old('description.en', $package->description['en'] ?? '');
    $price = old('price', $package->price);
    $discount = old('discounted_price', $package->discounted_price);
    $validity = old('validity_days', $package->validity_days);
    $washes = old('washes_count', $package->washes_count);
    $position = old('position', $package->sort_order);
    $isActiveOld = old('is_active', $package->is_active ? 1 : 0);
    $isActive = (bool) $isActiveOld;
@endphp

<div class="card">
    <form id="package_edit_form" action="{{ route('dashboard.packages.update', $package->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                <div class="col-lg-8">
                    {{-- بيانات الباقة --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">
                                    {{ __('packages.singular_title') }}
                                </span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    {{ __('services.basic_data_hint') }}
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('packages.name_ar') }}
                                    </label>
                                    <input type="text" name="name[ar]" class="form-control"
                                        value="{{ $nameAr }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('packages.name_en') }}
                                    </label>
                                    <input type="text" name="name[en]" class="form-control"
                                        value="{{ $nameEn }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('packages.description_ar') }}
                                    </label>
                                    <textarea name="description[ar]" class="form-control" rows="3">{{ $descAr }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('packages.description_en') }}
                                    </label>
                                    <textarea name="description[en]" class="form-control" rows="3">{{ $descEn }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- التسعير والصلاحية --}}
                    <div class="card">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">
                                    {{ __('services.pricing_block') }}
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('packages.price') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="price"
                                        class="form-control" value="{{ $price }}"
                                        placeholder="{{ __('packages.price_placeholder') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('packages.discount_price') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="discounted_price"
                                        class="form-control" value="{{ $discount }}"
                                        placeholder="{{ __('packages.price_placeholder') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('packages.validity_days') }}
                                    </label>
                                    <input type="number" min="1" name="validity_days" class="form-control"
                                        value="{{ $validity }}"
                                        placeholder="{{ __('packages.validity_days_placeholder') }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('packages.washes_count') }}
                                    </label>
                                    <input type="number" min="1" name="washes_count" class="form-control"
                                        value="{{ $washes }}"
                                        placeholder="{{ __('packages.washes_count_placeholder') }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('packages.position') }}
                                    </label>
                                    <input type="number" min="1" name="position" class="form-control"
                                        value="{{ $position }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الخدمات ضمن الباقة --}}
                    <div class="card mt-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">
                                    {{ __('packages.services') }}
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row">
                                <select name="services[]" class="form-select" multiple data-control="select2"
                                    data-placeholder="{{ __('packages.services_placeholder') }}">
                                    @foreach ($services as $service)
                                        @php
                                            $sName = $service->name[$locale] ?? (reset($service->name ?? []) ?? '');
                                        @endphp
                                        <option value="{{ $service->id }}"
                                            {{ in_array($service->id, $selectedServices ?? []) ? 'selected' : '' }}>
                                            {{ $sName }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- العمود الجانبي --}}
                <div class="col-lg-4">
                    {{-- صورة الباقة --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-4 mb-1">
                                    {{ __('packages.image') }}
                                </span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    {{ __('packages.image_hint') }}
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row mb-4">
                                <div class="image-input image-input-outline w-150px h-150px"
                                    data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px"
                                        style="background-image: url('{{ $imageArUrl }}')">
                                    </div>

                                    <label
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" title="{{ __('packages.image_ar') }}">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="image_ar" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="image_remove" />
                                    </label>

                                    <span
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" title="@lang('messages.cancel')">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">
                                    {{ __('packages.images_note') }}
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                            <div class="fv-row mb-4">
                                <div class="image-input image-input-outline w-150px h-150px"
                                    data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px"
                                        style="background-image: url('{{ $imageEnUrl }}')">
                                    </div>

                                    <label
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" title="{{ __('packages.image_en') }}">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="image_en" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="image_remove" />
                                    </label>

                                    <span
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" title="@lang('messages.cancel')">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">
                                    {{ __('packages.images_note') }}
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
                    </div>

                    {{-- الحالة --}}
                    <div class="card">
                        <div class="card-body">
                            <div class="fv-row mb-5">
                                <label class="fw-semibold fs-6 mb-2 d-block">
                                    {{ __('packages.status') }}
                                </label>
                                <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        {{ $isActive ? 'checked' : '' }} />
                                    <label class="form-check-label fw-semibold">
                                        {{ __('packages.active') }}
                                    </label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('packages.update') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('custom-script')
<script>
    (function() {
        const $form = $('#package_edit_form');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST', // نخليها POST عادي
            headers: {
                'X-HTTP-Method-Override': 'PUT' // ✅ هيدر إضافي يساعد Laravel يقرأها PUT
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __('packages.singular_title') }}',
                    text: res.message || '{{ __('packages.updated_successfully') }}',
                    timer: 2000,
                    showConfirmButton: false
                });

                if (res.redirect) {
                    window.location.href = res.redirect;
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                        globalAlertSelector: '#form_result'
                    });
                } else {
                    let msg = 'حدث خطأ غير متوقع.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('خطأ', msg, 'error');
                }
            },
            complete: function() {
                window.KH.setFormLoading($form, false);
            }
        });

    })();
</script>
@endpush
