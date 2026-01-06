@php
    /** @var \App\Models\Invoice $invoice */
@endphp

<div class="d-flex justify-content-end gap-2">

    @can('invoices.view')
        <a href="{{ route('dashboard.invoices.show', $invoice->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('invoices.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan

</div>