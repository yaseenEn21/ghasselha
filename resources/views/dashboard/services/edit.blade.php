@extends('base.layout.app')

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.services.index') }}" class="btn btn-light">
        رجوع لقائمة الخدمات
    </a>
@endsection

@php
    $locale = app()->getLocale();
    $imageArUrl = $service->getFirstMediaUrl('image_ar') ?: asset('assets/media/svg/files/blank-image.svg');
    $imageEnUrl = $service->getFirstMediaUrl('image_en') ?: asset('assets/media/svg/files/blank-image.svg');

    $nameAr = old('name.ar', $service->name['ar'] ?? '');
    $nameEn = old('name.en', $service->name['en'] ?? '');
    $descAr = old('description.ar', $service->description['ar'] ?? '');
    $descEn = old('description.en', $service->description['en'] ?? '');
    $duration = old('duration_minutes', $service->duration_minutes);
    $price = old('price', $service->price);
    $discountPrice = old('discounted_price', $service->discounted_price);
    $catId = old('service_category_id', $service->service_category_id);
    $isActiveOld = old('is_active', $service->is_active ? 1 : 0);
    $isActive = (bool) $isActiveOld;
@endphp

<div class="card">
    <form id="service_edit_form" action="{{ route('dashboard.services.update', $service->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                {{-- العمود الرئيسي --}}
                <div class="col-lg-8">
                    {{-- بيانات الخدمة --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">تعديل بيانات الخدمة</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    الاسم والوصف والتصنيف
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                {{-- تصنيف الخدمة --}}
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">تصنيف الخدمة</label>
                                    <select name="service_category_id" class="form-select">
                                        <option value="">اختر تصنيفاً</option>
                                        @foreach ($categories as $category)
                                            @php
                                                $catName = $category->name;
                                                if (is_array($catName)) {
                                                    $catName = $catName[$locale] ?? (reset($catName) ?? '');
                                                }
                                            @endphp
                                            <option value="{{ $category->id }}"
                                                {{ (int) $catId === (int) $category->id ? 'selected' : '' }}>
                                                {{ $catName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- اسم الخدمة بالعربية --}}
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">اسم الخدمة (AR)</label>
                                    <input type="text" name="name[ar]" class="form-control"
                                        value="{{ $nameAr }}" placeholder="مثال: غسيل خارجي" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- اسم الخدمة بالإنجليزية --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">اسم الخدمة (EN)</label>
                                    <input type="text" name="name[en]" class="form-control"
                                        value="{{ $nameEn }}" placeholder="Example: Exterior wash" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- الوصف AR --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">وصف الخدمة (AR)</label>
                                    <textarea name="description[ar]" class="form-control" rows="3" placeholder="وصف مختصر للخدمة">{{ $descAr }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- الوصف EN --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">وصف الخدمة (EN)</label>
                                    <textarea name="description[en]" class="form-control" rows="3" placeholder="Short description">{{ $descEn }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- التسعير والمدة --}}
                    <div class="card">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">المدة والتسعير</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-6">
                                {{-- المدة بالدقائق --}}
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">المدة (بالدقائق)</label>
                                    <input type="number" min="0" name="duration_minutes" class="form-control"
                                        value="{{ $duration }}" placeholder="مثال: 30" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- السعر --}}
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">السعر الأساسي</label>
                                    <input type="number" step="0.01" min="0" name="price"
                                        class="form-control" value="{{ $price }}" placeholder="0.00" />
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- السعر بعد الخصم --}}
                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">السعر بعد الخصم (اختياري)</label>
                                    <input type="number" step="0.01" min="0" name="discounted_price"
                                        class="form-control" value="{{ $discountPrice }}" placeholder="0.00" />
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
                                    <label class="fw-semibold fs-6 mb-2 d-block">الحالة</label>
                                    <div
                                        class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-6">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                            value="1" {{ $isActive ? 'checked' : '' }} />
                                        <label class="form-check-label fw-semibold">
                                            مفعّلة
                                        </label>
                                    </div>
                                    <div class="invalid-feedback d-block" data-error-for="is_active"></div>
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
                                <span class="card-label fw-bold fs-4 mb-1">صورة الخدمة (AR)</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    ستظهر في الواجهة العربية
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
                                        data-kt-image-input-action="change" title="تغيير الصورة">
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
                                <div class="form-text">الامتدادات المسموحة: png, jpg, jpeg, webp • أقصى حجم 2MB</div>
                                <div class="invalid-feedback d-block" data-error-for="image_ar"></div>
                            </div>
                        </div>
                    </div>

                    {{-- صورة الإنجليزية --}}
                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-4 mb-1">صورة الخدمة (EN)</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">
                                    ستظهر في الواجهة الإنجليزية
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row mb-4">
                                <div class="image-input image-input-outline w-150px h-150px"
                                    data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px"
                                        style="background-image: url('{{ $imageEnUrl }}')">
                                    </div>

                                    <label
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" title="تغيير الصورة">
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
                                <div class="form-text">الامتدادات المسموحة: png, jpg, jpeg, webp • أقصى حجم 2MB</div>
                                <div class="invalid-feedback d-block" data-error-for="image_en"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">تحديث الخدمة</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('custom-script')
<script>
    (function() {
        const $form = $('#service_edit_form');

        $form.on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            window.KH.setFormLoading($form, true, {
                text: 'جاري الحفظ...'
            });

            $.ajax({
                url: $form.attr('action'),
                type: 'POST', // عندنا @method('PUT') في الفورم
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم',
                        text: res.message || 'تم تحديث الخدمة بنجاح.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (res.redirect) {
                        window.location.href = res.redirect;
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // استخدام الهيلبر العام
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
