@php
    /** @var \App\Models\Employee $employee */
    $user = auth()->user();
@endphp

<div class="d-flex gap-2">

    @can('employees.view')
        <a href="{{ route('dashboard.employees.show', $employee->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('employees.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

    @can('employees.edit')
        <a href="{{ route('dashboard.employees.edit', $employee->id) }}"
           class="btn btn-icon btn-light-warning btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('employees.edit') }}">
            <i class="ki-duotone ki-pencil fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    @endcan

    @can('employees.delete')
        <button type="button"
                class="btn btn-icon btn-light-danger btn-sm js-delete-employee"
                data-id="{{ $employee->id }}"
                data-bs-toggle="tooltip"
                title="{{ __('employees.delete') }}">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    @endcan

</div>