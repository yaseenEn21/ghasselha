@extends('base.layout.app')

@section('content')

    @section('top-btns')
        <a href="{{ route('dashboard.packages.index') }}" class="btn btn-light">
            {{ __('packages.back_to_list') }}
        </a>
    @endsection

    <div class="card">
        <form id="package_create_form"
              action="{{ route('dashboard.packages.store') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

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
                                        <input type="text" name="name[ar]" class="form-control">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fw-semibold fs-6 mb-2">
                                            {{ __('packages.name_en') }}
                                        </label>
                                        <input type="text" name="name[en]" class="form-control">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6 fv-row">
                                        <label class="fw-semibold fs-6 mb-2">
                                            {{ __('packages.description_ar') }}
                                        </label>
                                        <textarea name="description[ar]" class="form-control" rows="3"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fw-semibold fs-6 mb-2">
                                            {{ __('packages.description_en') }}
                                        </label>
                                        <textarea name="description[en]" class="form-control" rows="3"></textarea>
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
                                        <input type="number" step="0.01" min="0"
                                               name="price" class="form-control"
                                               placeholder="{{ __('packages.price_placeholder') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-4 fv-row">
                                        <label class="fw-semibold fs-6 mb-2">
                                            {{ __('packages.discount_price') }}
                                        </label>
                                        <input type="number" step="0.01" min="0"
                                               name="discounted_price" class="form-control"
                                               placeholder="{{ __('packages.price_placeholder') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-4 fv-row">
                                        <label class="required fw-semibold fs-6 mb-2">
                                            {{ __('packages.validity_days') }}
                                        </label>
                                        <input type="number" min="1"
                                               name="validity_days" class="form-control"
                                               placeholder="{{ __('packages.validity_days_placeholder') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4 fv-row">
                                        <label class="required fw-semibold fs-6 mb-2">
                                            {{ __('packages.washes_count') }}
                                        </label>
                                        <input type="number" min="1"
                                               name="washes_count" class="form-control"
                                               placeholder="{{ __('packages.washes_count_placeholder') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4 fv-row">
                                        <label class="fw-semibold fs-6 mb-2">
                                            {{ __('packages.position') }}
                                        </label>
                                        <input type="number" min="1"
                                               name="position" class="form-control">
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
                                    <select name="services[]" class="form-select" multiple
                                            data-control="select2"
                                            data-placeholder="{{ __('packages.services_placeholder') }}">
                                        @php $locale = app()->getLocale(); @endphp
                                        @foreach($services as $service)
                                            @php
                                                $name = $service->name[$locale] ?? reset($service->name ?? []) ?? '';
                                            @endphp
                                            <option value="{{ $service->id }}">{{ $name }}</option>
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
                                             style="background-image: url('{{ asset('assets/media/svg/files/blank-image.svg') }}')">
                                        </div>

                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                               data-kt-image-input-action="change"
                                               title="{{ __('packages.image_ar') }}">
                                            <i class="ki-duotone ki-pencil fs-7">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <input type="file" name="image_ar" accept=".png, .jpg, .jpeg, .webp" />
                                            <input type="hidden" name="image_remove" />
                                        </label>

                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
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
                                             style="background-image: url('{{ asset('assets/media/svg/files/blank-image.svg') }}')">
                                        </div>

                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                               data-kt-image-input-action="change"
                                               title="{{ __('packages.image_en') }}">
                                            <i class="ki-duotone ki-pencil fs-7">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <input type="file" name="image_en" accept=".png, .jpg, .jpeg, .webp" />
                                            <input type="hidden" name="image_remove" />
                                        </label>

                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
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
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked />
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
                    <span class="indicator-label">{{ __('packages.save') }}</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('custom-script')
    <script>
        (function () {
            const $form = $('#package_create_form');

            $form.on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData($form[0]);

                window.KH.setFormLoading($form, true, {text: 'جاري الحفظ...'});

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('packages.singular_title') }}',
                            text: res.message || '{{ __('packages.created_successfully') }}',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        if (res.redirect) {
                            window.location.href = res.redirect;
                        }
                    },
                    error: function (xhr) {
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
                    complete: function () {
                        window.KH.setFormLoading($form, false);
                    }
                });
            });
        })();
    </script>
@endpush