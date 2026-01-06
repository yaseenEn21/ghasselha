@php
    /** @var \App\Models\Promotion $promotion */
    /** @var \App\Models\PromotionCoupon $coupon */
@endphp

<div class="d-flex gap-2 justify-content-end">

    @can('promotions.view')
        <a href="{{ route('dashboard.promotions.coupons.redemptions', [$promotion->id, $coupon->id]) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('promotions.coupons.redemptions_title') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

    @can('promotions.edit')
        <a href="{{ route('dashboard.promotions.coupons.edit', [$promotion->id, $coupon->id]) }}"
           class="btn btn-icon btn-light-warning btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('promotions.coupons.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    @endcan

    @can('promotions.edit')
        <button type="button"
                class="btn btn-icon btn-light-danger btn-sm js-delete-coupon"
                data-id="{{ $coupon->id }}"
                data-bs-toggle="tooltip"
                title="{{ __('promotions.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    @endcan

</div>