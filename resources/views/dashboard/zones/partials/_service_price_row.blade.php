@php
    $serviceName = $sp->service ? i18n($sp->service->name) : '—';
@endphp

<tr id="sp_row_{{ $sp->id }}">
    <td>{{ $sp->id }}</td>

    <td>
        <div class="d-flex flex-column">
            <span class="fw-bold">{{ $serviceName }}</span>
            @if($sp->service)
                <span class="text-muted fs-7">
                    {{ __('zones.service_prices.base_price') }}:
                    {{ number_format((float)$sp->service->price, 2) }}
                    @if($sp->service->discounted_price !== null)
                        • {{ __('zones.service_prices.base_discounted') }}:
                        {{ number_format((float)$sp->service->discounted_price, 2) }}
                    @endif
                </span>
            @endif
        </div>
    </td>

    <td>
        <span class="badge badge-light-primary">
            {{ __('zones.time_periods.' . $sp->time_period) }}
        </span>
    </td>

    <td>{{ number_format((float)$sp->price, 2) }}</td>

    <td>
        {{ $sp->discounted_price !== null ? number_format((float)$sp->discounted_price, 2) : '—' }}
    </td>

    <td>
        @if($sp->is_active)
            <span class="badge badge-light-success">{{ __('zones.active') }}</span>
        @else
            <span class="badge badge-light-danger">{{ __('zones.inactive') }}</span>
        @endif
    </td>

    <td>{{ optional($sp->created_at)->format('Y-m-d') }}</td>

    <td class="text-end">
        <button type="button"
                class="btn btn-sm btn-icon btn-light-primary btn-edit-sp"
                data-id="{{ $sp->id }}">
            <i class="ki-duotone ki-pencil fs-4">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </button>

        <button type="button"
                class="btn btn-sm btn-icon btn-light-danger btn-delete-sp"
                data-id="{{ $sp->id }}">
            <i class="ki-duotone ki-trash fs-4">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
        </button>
    </td>
</tr>