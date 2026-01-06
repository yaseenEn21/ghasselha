<div class="d-flex gap-2">

    @can('services.view')
        <a href="{{ route('dashboard.services.show', $service->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('services.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

    @can('services.edit')
        <a href="{{ route('dashboard.services.edit', $service->id) }}"
           class="btn btn-icon btn-light-warning btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('services.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    @endcan

    @can('services.delete')
        <button type="button"
                class="btn btn-icon btn-light-danger btn-sm js-delete-service"
                data-id="{{ $service->id }}"
                data-bs-toggle="tooltip"
                title="{{ __('services.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    @endcan

</div>