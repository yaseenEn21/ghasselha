@can('zones.edit')
    <a href="{{ route('dashboard.zones.edit', $row->id) }}" class="btn btn-sm btn-light-primary me-2">
        {{ __('zones.edit') }}
    </a>
@endcan

@can('zones.delete')
    <button type="button" class="btn btn-sm btn-light-danger js-delete-zone" data-id="{{ $row->id }}">
        {{ __('zones.delete') }}
    </button>
@endcan