@php
    /** @var \App\Models\Promotion $promotion */
@endphp

<div class="d-flex justify-content-end gap-2">

    @can('promotions.view')
        <a href="{{ route('dashboard.promotions.show', $promotion->id) }}" class="btn btn-icon btn-light-info btn-sm"
            data-bs-toggle="tooltip" title="{{ __('promotions.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
        </a>
    @endcan

    @can('promotions.edit')
        <a href="{{ route('dashboard.promotions.edit', $promotion->id) }}" class="btn btn-icon btn-light-warning btn-sm"
            data-bs-toggle="tooltip" title="{{ __('promotions.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </a>
    @endcan

    @can('promotion_coupons.view')
        <a href="{{ route('dashboard.promotions.coupons.index', $promotion->id) }}"
            class="btn btn-icon btn-light-primary btn-sm" data-bs-toggle="tooltip"
            title="{{ __('promotions.coupons_manage') }}">
            <i class="ki-duotone ki-category">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
            </i>
        </a>
    @endcan

    @can('promotions.delete')
        <button type="button" class="btn btn-icon btn-light-danger btn-sm js-delete-promotion"
            data-id="{{ $promotion->id }}" data-bs-toggle="tooltip" title="{{ __('promotions.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </button>
    @endcan

</div>
