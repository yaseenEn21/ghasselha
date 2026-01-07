@can('customer_groups.edit')
    <a href="{{ route('dashboard.customer-groups.edit', $row->id) }}" class="btn btn-sm btn-light-primary me-2">
        {{ __('customer_groups.edit') }}
    </a>
@endcan

@can('customer_groups.delete')
    <button type="button" class="btn btn-sm btn-light-danger js-delete-group" data-id="{{ $row->id }}">
        {{ __('customer_groups.delete') }}
    </button>
@endcan