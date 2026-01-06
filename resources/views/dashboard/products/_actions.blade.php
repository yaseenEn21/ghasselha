@php
    /** @var \App\Models\Product $product */
@endphp

<div class="d-flex gap-2">

    @can('products.view')
        <a href="{{ route('dashboard.products.show', $product->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('products.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

    @can('products.edit')
        <a href="{{ route('dashboard.products.edit', $product->id) }}"
           class="btn btn-icon btn-light-warning btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('products.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    @endcan

    @can('products.delete')
        <button type="button"
                class="btn btn-icon btn-light-danger btn-sm js-delete-product"
                data-id="{{ $product->id }}"
                data-bs-toggle="tooltip"
                title="{{ __('products.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    @endcan

</div>
