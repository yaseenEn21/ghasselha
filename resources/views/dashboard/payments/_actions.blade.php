@php
    /** @var \App\Models\Payment $payment */
@endphp

<div class="d-flex justify-content-end gap-2">
    @can('payments.view')
        <a href="{{ route('dashboard.payments.show', $payment->id) }}"
           class="btn btn-icon btn-light-info btn-sm"
           data-bs-toggle="tooltip"
           title="{{ __('payments.view') }}">
            <i class="ki-duotone ki-eye fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    @endcan
</div>