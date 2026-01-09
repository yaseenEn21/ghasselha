<div class="d-flex justify-content-end gap-2">
    @can('bookings.view')
        <a href="{{ route('dashboard.bookings.show', $booking->id) }}"
           class="btn btn-sm btn-light-primary">
            <i class="ki-duotone ki-eye fs-5 me-1">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            {{ __('bookings.view') }}
        </a>
    @endcan
</div>