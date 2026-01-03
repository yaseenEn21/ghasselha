{{-- resources/views/dashboard/package_subscriptions/show.blade.php --}}
@extends('base.layout.app')

@section('content')

    @section('top-btns')
        @can('package_subscriptions.edit')
            <a href="{{ route('dashboard.package-subscriptions.edit', $subscription->id) }}" class="btn btn-primary">
                {{ __('package_subscriptions.edit') }}
            </a>
        @endcan
    @endsection

    @php
        $locale = app()->getLocale();

        $package = $subscription->package;
        $user    = $subscription->user;

        // اسم الباقة باللوكال
        $packageName = null;
        if ($package) {
            $rawName = $package->name;
            if (is_array($rawName)) {
                $packageName = $rawName[$locale] ?? (reset($rawName) ?: '');
            } else {
                $packageName = $rawName;
            }
        }

        $statusBadgeClass = match ($subscription->status) {
            'active'    => 'badge-light-success',
            'expired'   => 'badge-light-warning',
            'cancelled' => 'badge-light-danger',
            default     => 'badge-light-secondary',
        };

        $statusLabel = match ($subscription->status) {
            'active'    => __('package_subscriptions.status_active'),
            'expired'   => __('package_subscriptions.status_expired'),
            'cancelled' => __('package_subscriptions.status_cancelled'),
            default     => $subscription->status,
        };
    @endphp

    <div class="row g-6 g-xl-9">

        {{-- العمود الأيسر: ملخص الاشتراك --}}
        <div class="col-xl-4">

            <div class="card mb-6">
                <div class="card-body d-flex flex-column">

                    <div class="d-flex align-items-center mb-5">
                        <div class="symbol symbol-60px symbol-circle me-4">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-badge fs-2x text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                                <h2 class="fw-bold mb-0">
                                    {{ $packageName ?: __('package_subscriptions.singular_title') . ' #' . $subscription->id }}
                                </h2>

                                <span class="badge {{ $statusBadgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            @if ($user)
                                <div class="text-muted fw-semibold">
                                    {{ $user->name }} @if($user->mobile) • {{ $user->mobile }} @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="separator my-4"></div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('package_subscriptions.package') }}</span>
                            <span class="fw-bold">
                                {{ $packageName ?: '—' }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('package_subscriptions.customer') }}</span>
                            <span class="fw-bold">
                                {{ $user?->name ?? '—' }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('package_subscriptions.mobile') }}</span>
                            <span class="fw-bold">
                                {{ $user?->mobile ?? '—' }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('package_subscriptions.period') }}</span>
                            <span class="fw-bold">
                                @if($subscription->starts_at && $subscription->ends_at)
                                    {{ $subscription->starts_at->format('Y-m-d') }}
                                    →
                                    {{ $subscription->ends_at->format('Y-m-d') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('package_subscriptions.remaining_washes') }}</span>
                            <span class="fw-bold">
                                {{ $subscription->remaining_washes }} / {{ $subscription->total_washes_snapshot }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('package_subscriptions.final_price') }}</span>
                            <span class="fw-bold">
                                {{ number_format($subscription->final_price_snapshot, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="separator my-4"></div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('package_subscriptions.purchased_at') }}</span>
                            <span class="fw-semibold">
                                {{ optional($subscription->purchased_at)->format('Y-m-d H:i') ?: '—' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('packages.created_at') }}</span>
                            <span class="fw-semibold">
                                {{ optional($subscription->created_at)->format('Y-m-d') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('packages.updated_at') }}</span>
                            <span class="fw-semibold">
                                {{ optional($subscription->updated_at)->format('Y-m-d H:i') }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- العمود الأيمن: تفاصيل السناب شوت + الباقة --}}
        <div class="col-xl-8">

            {{-- سناب شوت الأسعار والغسلات --}}
            <div class="card mb-6">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            {{ __('package_subscriptions.singular_title') }}
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">
                            {{ __('packages.package_details_hint') }}
                        </span>
                    </h3>
                </div>

                <div class="card-body pt-0">
                    <div class="row g-6 mb-6">
                        <div class="col-md-4">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.basic_price') }}
                                </div>
                                <div class="fs-4 fw-bold">
                                    {{ number_format($subscription->price_snapshot, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.discounted_price') }}
                                </div>
                                <div class="fs-4 fw-bold">
                                    @if(!is_null($subscription->discounted_price_snapshot))
                                        {{ number_format($subscription->discounted_price_snapshot, 2) }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.final_price') }}
                                </div>
                                <div class="fs-4 fw-bold text-primary">
                                    {{ number_format($subscription->final_price_snapshot, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-6">
                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.washes_count') }}
                                </div>
                                <div class="fs-4 fw-bold">
                                    {{ $subscription->total_washes_snapshot }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('package_subscriptions.remaining_washes') }}
                                </div>
                                <div class="fs-4 fw-bold">
                                    {{ $subscription->remaining_washes }} / {{ $subscription->total_washes_snapshot }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- لمحة عن الباقة الأصلية (اختياري) --}}
            @if($package)
                <div class="card">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">
                                {{ __('packages.singular_title') }}
                            </span>
                            <span class="text-muted mt-1 fw-semibold fs-7">
                                {{ $packageName }}
                            </span>
                        </h3>
                    </div>

                    <div class="card-body pt-0">
                        <div class="row g-6">
                            <div class="col-md-4">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.validity_days') }}
                                </div>
                                <div class="fs-5 fw-bold">
                                    {{ $package->validity_days }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.washes_count') }}
                                </div>
                                <div class="fs-5 fw-bold">
                                    {{ $package->washes_count }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.sort_order') }}
                                </div>
                                <div class="fs-5 fw-bold">
                                    #{{ $package->sort_order }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>
@endsection