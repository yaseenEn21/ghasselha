@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('promotion_coupons.view')
        <a href="{{ route('dashboard.promotions.coupons.index', $promotion->id) }}" class="btn btn-light-primary">
            {{ __('promotions.coupons_manage') }}
        </a>
    @endcan
@endsection

@php
    $locale = app()->getLocale();
    $name = $promotion->name[$locale] ?? (collect($promotion->name ?? [])->first() ?? '—');
    $desc = $promotion->description[$locale] ?? (collect($promotion->description ?? [])->first() ?? null);

    $discountLabel = $promotion->discount_type === 'percent'
        ? rtrim(rtrim(number_format((float)$promotion->discount_value, 2), '0'), '.') . '%'
        : number_format((float)$promotion->discount_value, 2) . ' SAR';
@endphp

<div class="row g-6">
    <div class="col-xl-8">
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('promotions.promotion_details') }}</h3>
            </div>
            <div class="card-body pt-0">
                <div class="fw-bold fs-2 mb-2">{{ $name }}</div>
                <div class="text-muted mb-4">{{ $desc ?: '—' }}</div>

                <div class="d-flex flex-wrap gap-4">

                    <div>
                        <div class="text-muted fs-8">{{ __('promotions.fields.discount') }}</div>
                        <div class="fw-semibold">{{ $discountLabel }}</div>
                    </div>

                    <div>
                        <div class="text-muted fs-8">{{ __('promotions.fields.period') }}</div>
                        <div class="fw-semibold">
                            {{ $promotion->starts_at?->format('Y-m-d') ?? '—' }} → {{ $promotion->ends_at?->format('Y-m-d') ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-muted fs-8">{{ __('promotions.fields.status') }}</div>
                        <div class="fw-semibold">
                            @if($promotion->is_active)
                                <span class="badge badge-light-success">{{ __('promotions.active') }}</span>
                            @else
                                <span class="badge badge-light-danger">{{ __('promotions.inactive') }}</span>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush">
            <div class="card-header pt-5">
                <h3 class="card-title fw-bold">{{ __('promotions.quick_actions') }}</h3>
            </div>
            <div class="card-body pt-0">
                @can('promotion_coupons.create')
                    <a href="{{ route('dashboard.promotions.coupons.create', $promotion->id) }}" class="btn btn-primary w-100 mb-3">
                        {{ __('promotions.add_coupon') }}
                    </a>
                @endcan

                <a href="{{ route('dashboard.promotions.coupons.index', $promotion->id) }}" class="btn btn-light w-100">
                    {{ __('promotions.coupons_manage') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection