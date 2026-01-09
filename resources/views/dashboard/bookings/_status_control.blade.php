@php
    $status = $booking->status ?? 'pending';

    $badgeClass = match($status) {
        'pending' => 'badge-light-warning',
        'confirmed' => 'badge-light-primary',
        'moving' => 'badge-light-info',
        'arrived' => 'badge-light-primary',
        'completed' => 'badge-light-success',
        'cancelled' => 'badge-light-danger',
        default => 'badge-light'
    };

    $locked = in_array($status, ['completed','cancelled'], true);

    $options = [
        'pending',
        'confirmed',
        'moving',
        'arrived',
        'completed',
        'cancelled',
    ];
@endphp

<div class="d-flex align-items-center gap-2">
    {{-- Always show current badge --}}
    {{-- <span class="badge {{ $badgeClass }}">
        {{ __('bookings.status.'.$status) }}
    </span> --}}

    @if(!$locked)
        <select class="form-select form-select-sm w-150px js-booking-status-select"
                data-id="{{ $booking->id }}"
                data-url="{{ route('dashboard.bookings.status.update', $booking->id) }}">
            @foreach($options as $opt)
                <option value="{{ $opt }}" {{ $opt === $status ? 'selected' : '' }}>
                    {{ __('bookings.status.'.$opt) }}
                </option>
            @endforeach
        </select>
    @endif
</div>