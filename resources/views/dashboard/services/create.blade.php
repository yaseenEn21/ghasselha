@extends('base.layout.app')

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.services.index') }}" class="btn btn-light">
        {{ __('services.back_to_list') }}
    </a>
@endsection

<div class="card">
    <form id="service_create_form" action="{{ route('dashboard.services.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                {{-- العمود الرئيسي --}}
                <div class="col-lg-8">
                    {{-- بيانات الخدمة --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">
                                    {{ __('services.basic_data') }}
                                </span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    {{ __('services.basic_data_hint') }}
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                {{-- تصنيف الخدمة --}}
                                <div class="col-12 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('services.category') }}
                                    </label>
                                    <select name="service_category_id" class="form-select">
                                        <option value="">
                                            {{ __('services.select_category') }}
                                        </option>
                                        @php $locale = app()->getLocale(); @endphp
                                        @foreach ($categories as $category)
                                            @php
                                                $catName = $category->name;
                                                if (is_array($catName)) {
                                                    $catName = $catName[$locale] ?? (reset($catName) ?? '');
                                                }
                                            @endphp
                                            <option value="{{ $category->id }}">{{ $catName }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- اسم الخدمة بالعربية --}}
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('services.name_ar') }}
                                    </label>
                                    <input type="text" name="name[ar]" class="form-control"
                                        placeholder="مثال: غسيل خارجي" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- اسم الخدمة بالإنجليزية --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('services.name_en') }}
                                    </label>
                                    <input type="text" name="name[en]" class="form-control"
                                        placeholder="Example: Exterior wash" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- الوصف AR --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('services.description_ar') }}
                                    </label>
                                    <textarea name="description[ar]" class="form-control" rows="3" placeholder="وصف مختصر للخدمة"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- الوصف EN --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('services.description_en') }}
                                    </label>
                                    <textarea name="description[en]" class="form-control" rows="3" placeholder="Short description"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- التسعير والمدة --}}
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
                                {{-- المدة بالدقائق --}}
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('services.duration') }}
                                    </label>
                                    <input type="number" min="0" name="duration_minutes" class="form-control"
                                        placeholder="{{ __('services.duration_placeholder') }}" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- السعر --}}
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">
                                        {{ __('services.price') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="price"
                                        class="form-control" placeholder="{{ __('services.price_placeholder') }}" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- السعر بعد الخصم --}}
                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('services.discount_price') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="discounted_price"
                                        class="form-control" placeholder="{{ __('services.price_placeholder') }}" />
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الحالة --}}
                    <div class="card mt-7">
                        <div class="card-body">
                            <div class="row mb-5">
                                
                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">
                                        {{ __('services.sort_order') }}
                                    </label>
                                    <input type="number" name="sort_order" class="form-control" min="1"
                                        value="{{ old('sort_order', $service->sort_order ?? '') }}"
                                        placeholder="مثال: 1">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-semibold fs-6 mb-2 d-block">
                                        {{ __('services.status') }}
                                    </label>
                                    <div
                                        class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-6">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                            value="1" checked />
                                        <label class="form-check-label fw-semibold">
                                            {{ __('services.active') }}
                                        </label>
                                    </div>
                                    <div class="invalid-feedback d-block"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- العمود الجانبي: الصور والحالة --}}
                <div class="col-lg-4">
                    {{-- صورة العربية --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-4 mb-1">
                                    {{ __('services.image_ar') }}
                                </span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    {{ __('services.image_ar_hint') }}
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

                                    <label
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" title="{{ __('services.image_ar') }}">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="image_ar" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="image_ar_remove" />
                                    </label>

                                    <span
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" title="@lang('messages.cancel', [], app()->getLocale())">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">
                                    {{ __('services.images_note') }}
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
                    </div>

                    {{-- صورة الإنجليزية --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-4 mb-1">
                                    {{ __('services.image_en') }}
                                </span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    {{ __('services.image_en_hint') }}
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

                                    <label
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" title="{{ __('services.image_en') }}">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="image_en" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="image_en_remove" />
                                    </label>

                                    <span
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" title="@lang('messages.cancel', [], app()->getLocale())">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">
                                    {{ __('services.images_note') }}
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
                <span class="indicator-label">
                    {{ __('services.save') }}
                </span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('custom-script')
<script>
    (function() {
        const $form = $('#service_create_form');

        $form.on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            window.KH.setFormLoading($form, true, {
                text: 'جاري الحفظ...'
            });

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('services.singular_title') }}',
                        text: res.message ||
                            '{{ __('services.created_successfully') }}',
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
        });
    })();
</script>
@endpush
