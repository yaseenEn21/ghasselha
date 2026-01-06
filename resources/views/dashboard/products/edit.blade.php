@extends('base.layout.app')

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.products.index') }}" class="btn btn-light">
        {{ __('products.back_to_list') }}
    </a>
@endsection

@php
    $imgAr = $product->getFirstMediaUrl('image_ar') ?: asset('assets/media/svg/files/blank-image.svg');
    $imgEn = $product->getFirstMediaUrl('image_en') ?: asset('assets/media/svg/files/blank-image.svg');
@endphp

<div class="card">
    <form id="product_edit_form" action="{{ route('dashboard.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                <div class="col-lg-8">

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">{{ __('products.basic_data') }}</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ __('products.basic_data_hint') }}</span>
                            </h3>
                        </div>

                        <div class="card-body pt-0">
                            <div class="row g-6">

                                <div class="col-12 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.category') }}</label>
                                    <select name="product_category_id" class="form-select">
                                        <option value="">{{ __('products.select_category_optional') }}</option>
                                        @php $locale = app()->getLocale(); @endphp
                                        @foreach ($categories as $category)
                                            @php $catName = $category->name ?? []; $catName = $catName[$locale] ?? (reset($catName) ?: ''); @endphp
                                            <option value="{{ $category->id }}" {{ (int)$product->product_category_id === (int)$category->id ? 'selected' : '' }}>
                                                {{ $catName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">{{ __('products.name_ar') }}</label>
                                    <input type="text" name="name[ar]" class="form-control" value="{{ $product->name['ar'] ?? '' }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.name_en') }}</label>
                                    <input type="text" name="name[en]" class="form-control" value="{{ $product->name['en'] ?? '' }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.description_ar') }}</label>
                                    <textarea name="description[ar]" class="form-control" rows="3">{{ $product->description['ar'] ?? '' }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.description_en') }}</label>
                                    <textarea name="description[en]" class="form-control" rows="3">{{ $product->description['en'] ?? '' }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">{{ __('products.pricing_block') }}</span>
                            </h3>
                        </div>

                        <div class="card-body pt-0">
                            <div class="row g-6">
                                <div class="col-md-4 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">{{ __('products.price') }}</label>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ $product->price }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.discounted_price') }}</label>
                                    <input type="number" step="0.01" min="0" name="discounted_price" class="form-control" value="{{ $product->discounted_price }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.max_qty_per_booking') }}</label>
                                    <input type="number" min="1" name="max_qty_per_booking" class="form-control" value="{{ $product->max_qty_per_booking }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-7">
                        <div class="card-body">
                            <div class="row mb-5">

                                <div class="col-md-4 fv-row">
                                    <label class="fw-semibold fs-6 mb-2">{{ __('products.sort_order') }}</label>
                                    <input type="number" name="sort_order" class="form-control" min="1" value="{{ $product->sort_order }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-semibold fs-6 mb-2 d-block">{{ __('products.status') }}</label>
                                    <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-6">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} />
                                        <label class="form-check-label fw-semibold">{{ __('products.active') }}</label>
                                    </div>
                                    <div class="invalid-feedback d-block"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-4 mb-1">{{ __('products.image_ar') }}</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ __('products.image_ar_hint') }}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row mb-4">
                                <div class="image-input image-input-outline w-150px h-150px" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ $imgAr }}')"></div>

                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                           data-kt-image-input-action="change">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="image_ar" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="image_ar_remove" />
                                    </label>

                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                          data-kt-image-input-action="cancel">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">{{ __('products.images_note') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-4 mb-1">{{ __('products.image_en') }}</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ __('products.image_en_hint') }}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row mb-4">
                                <div class="image-input image-input-outline w-150px h-150px" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ $imgEn }}')"></div>

                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                           data-kt-image-input-action="change">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="image_en" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="image_en_remove" />
                                    </label>

                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                          data-kt-image-input-action="cancel">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">{{ __('products.images_note') }}</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('products.update') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('custom-script')
<script>
    (function() {
        const $form = $('#product_edit_form');

        $form.on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            window.KH.setFormLoading($form, true, { text: '{{ app()->getLocale()==="ar" ? "جاري الحفظ..." : "Saving..." }}' });

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('products.singular_title') }}',
                        text: res.message || '{{ __('products.updated_successfully') }}',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (res.redirect) window.location.href = res.redirect;
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                            globalAlertSelector: '#form_result'
                        });
                    } else {
                        let msg = '{{ app()->getLocale()==="ar" ? "حدث خطأ غير متوقع." : "Unexpected error." }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire('{{ app()->getLocale()==="ar" ? "خطأ" : "Error" }}', msg, 'error');
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