@php
    /** @var \App\Models\Package $package */
    $user = auth()->user();
@endphp

<div class="d-flex gap-2">

    {{-- 👁 عرض --}}
    @can('packages.view')
        <a href="{{ route('dashboard.packages.show', $package->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('packages.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

    {{-- ✏ تعديل --}}
    @can('packages.edit')
        <a href="{{ route('dashboard.packages.edit', $package->id) }}"
           class="btn btn-icon btn-light-warning btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('packages.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    @endcan

    {{-- 🗑 حذف --}}
    @can('packages.delete')
        <button type="button"
                class="btn btn-icon btn-light-danger btn-sm js-delete-package"
                data-id="{{ $package->id }}"
                data-bs-toggle="tooltip"
                title="{{ __('packages.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    @endcan

</div>