@can('customer_groups.view')
    <a href="{{ route('dashboard.customer-groups.show', $row->id) }}" class="btn btn-sm btn btn-icon btn-light-info me-2">
        <i class="ki-duotone ki-eye fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </a>
@endcan

@can('customer_groups.edit')
    <a href="{{ route('dashboard.customer-groups.edit', $row->id) }}" class="btn btn-sm btn btn-icon btn-light-warning me-2">
        <i class="ki-duotone ki-pencil fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
@endcan

@can('customer_groups.delete')
    <button type="button" class="btn btn-sm btn btn-icon btn-light-danger js-delete-group" data-id="{{ $row->id }}">
        <i class="ki-duotone ki-trash fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </button>
@endcan