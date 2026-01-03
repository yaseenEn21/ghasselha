@php
    /** @var \App\Models\PackageSubscription $subscription */
    $user = auth()->user();
@endphp

<div class="d-flex gap-2">

    {{-- 👁 عرض --}}
    @can('package_subscriptions.view')
        <a href="{{ route('dashboard.package-subscriptions.show', $subscription->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('package_subscriptions.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

    {{-- ✏ تعديل --}}
    @can('package_subscriptions.edit')
        <a href="{{ route('dashboard.package-subscriptions.edit', $subscription->id) }}"
           class="btn btn-icon btn-light-warning btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('package_subscriptions.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    @endcan

    {{-- 🗑 حذف --}}
    @can('package_subscriptions.delete')
        <button type="button"
                class="btn btn-icon btn-light-danger btn-sm js-delete-package-subscription"
                data-id="{{ $subscription->id }}"
                data-bs-toggle="tooltip"
                title="{{ __('package_subscriptions.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    @endcan

</div>