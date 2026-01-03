@extends('base.layout.app')

@section('content')

    @section('top-btns')
        <a href="{{ route('dashboard.packages.index') }}" class="btn btn-light">
            {{ __('packages.title') }}
        </a>

        @can('packages.edit')
            <a href="{{ route('dashboard.packages.edit', $package->id) }}" class="btn btn-primary">
                {{ __('packages.edit') }}
            </a>
        @endcan
    @endsection

    @php
        $locale = app()->getLocale();

        // الاسم والوصف حسب اللغة
        $name = $package->name[$locale]
            ?? (is_array($package->name) ? (reset($package->name) ?: '') : $package->name);

        $description = $package->description[$locale]
            ?? (is_array($package->description) ? (reset($package->description) ?: '') : $package->description);

        // السعر النهائي (لو في خصم نعرضه)
        $finalPrice = $package->discounted_price ?: $package->price;

        // إحصائيات الاشتراكات
        $totalSubscriptions   = $package->subscriptions->count();
        $uniqueCustomers      = $package->subscriptions->pluck('user_id')->filter()->unique()->count();
        $activeSubscriptions  = $package->subscriptions->where('status', 'active')->count();
        $expiredSubscriptions = $package->subscriptions->where('status', 'expired')->count();
        $cancelledSubscriptions = $package->subscriptions->where('status', 'cancelled')->count();
    @endphp

    <div class="row g-6 g-xl-9">

        {{-- العمود الأيسر: ملخص الباقة + الإحصائيات --}}
        <div class="col-xl-4">

            {{-- بطاقة ملخص الباقة --}}
            <div class="card mb-6">
                <div class="card-body d-flex flex-column">

                    {{-- العنوان والحالة --}}
                    <div class="d-flex align-items-center mb-5">
                        <div class="symbol symbol-60px symbol-circle me-4">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-gift fs-2x text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                                <h2 class="fw-bold mb-0">
                                    {{ $name ?: __('packages.singular_title') . ' #' . $package->id }}
                                </h2>

                                @if ($package->is_active)
                                    <span class="badge badge-light-success">
                                        {{ __('packages.active') }}
                                    </span>
                                @else
                                    <span class="badge badge-light-danger">
                                        {{ __('packages.inactive') }}
                                    </span>
                                @endif
                            </div>

                            @if ($description)
                                <div class="text-muted fw-semibold">
                                    {{ \Illuminate\Support\Str::limit($description, 120) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="separator my-4"></div>

                    {{-- الأسعار والصلاحية --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('packages.basic_price') }}</span>
                            <span class="fw-bold">
                                {{ number_format($package->price, 2) }}
                                {{ __('packages.currency_suffix') }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('packages.discounted_price') }}</span>
                            <span class="fw-bold">
                                @if (!is_null($package->discounted_price))
                                    {{ number_format($package->discounted_price, 2) }}
                                    {{ __('packages.currency_suffix') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('packages.final_price') }}</span>
                            <span class="fw-bold text-primary">
                                {{ number_format($finalPrice, 2) }}
                                {{ __('packages.currency_suffix') }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('packages.validity_days') }}</span>
                            <span class="fw-bold">{{ $package->validity_days }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('packages.washes_count') }}</span>
                            <span class="fw-bold">{{ $package->washes_count }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('packages.sort_order') }}</span>
                            <span class="fw-bold">#{{ $package->sort_order }}</span>
                        </div>
                    </div>

                    <div class="separator my-4"></div>

                    {{-- تواريخ الإنشاء والتحديث --}}
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('packages.created_at') }}</span>
                            <span class="fw-semibold">
                                {{ optional($package->created_at)->format('Y-m-d') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('packages.updated_at') }}</span>
                            <span class="fw-semibold">
                                {{ optional($package->updated_at)->format('Y-m-d H:i') }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- بطاقة عدد المشتركين --}}
            <div class="card card-flush mb-4">
                <div class="card-body d-flex flex-column justify-content-center py-5">
                    <div class="d-flex align-items-center mb-3">
                        <div class="symbol symbol-40px symbol-circle me-3">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-duotone ki-people fs-2 text-info">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div>
                            <div class="fs-6 text-muted">
                                {{ __('packages.unique_customers') }}
                            </div>
                            <div class="fs-2 fw-bold">
                                {{ $uniqueCustomers }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div class="text-muted fs-7">
                            {{ __('packages.total_subscriptions') }}
                        </div>
                        <div class="fw-semibold">
                            {{ $totalSubscriptions }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- بطاقة حالة الاشتراكات --}}
            <div class="card card-flush">
                <div class="card-body py-5">

                    <div class="d-flex align-items-center mb-3">
                        <div class="symbol symbol-40px symbol-circle me-3">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-graph-up fs-2 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div>
                            <div class="fs-6 text-muted">
                                {{ __('packages.subscriptions_stats') }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between">
                            <span class="badge badge-light-success">
                                {{ __('packages.status_active') }}
                            </span>
                            <span class="fw-bold">{{ $activeSubscriptions }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="badge badge-light-warning">
                                {{ __('packages.status_expired') }}
                            </span>
                            <span class="fw-bold">{{ $expiredSubscriptions }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="badge badge-light-danger">
                                {{ __('packages.status_cancelled') }}
                            </span>
                            <span class="fw-bold">{{ $cancelledSubscriptions }}</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- العمود الأيمن: التفاصيل + الخدمات --}}
        <div class="col-xl-8">

            {{-- تفاصيل الباقة --}}
            <div class="card mb-6">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            {{ __('packages.package_details') }}
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">
                            {{ __('packages.package_details_hint') }}
                        </span>
                    </h3>
                </div>

                <div class="card-body pt-0">

                    {{-- الاسم بالعربي والإنجليزي --}}
                    <div class="row mb-6">
                        <div class="col-md-6">
                            <div class="mb-2 text-muted fw-semibold">
                                {{ __('packages.name_ar') }}
                            </div>
                            <div class="fw-bold fs-6">
                                {{ $package->name['ar'] ?? '—' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2 text-muted fw-semibold">
                                {{ __('packages.name_en') }}
                            </div>
                            <div class="fw-bold fs-6">
                                {{ $package->name['en'] ?? '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- وصف الباقة --}}
                    @if ($description)
                        <div class="mb-6">
                            <div class="mb-2 text-muted fw-semibold">
                                {{ __('packages.package_description') }}
                            </div>
                            <p class="text-gray-700 mb-0">
                                {{ $description }}
                            </p>
                        </div>
                    @endif

                    {{-- الأسعار مرة أخرى بشكل مبسّط (اختياري) --}}
                    <div class="row g-6">
                        <div class="col-md-4">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.basic_price') }}
                                </div>
                                <div class="fs-4 fw-bold">
                                    {{ number_format($package->price, 2) }}
                                    {{ __('packages.currency_suffix') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-4 h-100">
                                <div class="text-muted fw-semibold mb-1">
                                    {{ __('packages.discounted_price') }}
                                </div>
                                <div class="fs-4 fw-bold">
                                    @if (!is_null($package->discounted_price))
                                        {{ number_format($package->discounted_price, 2) }}
                                        {{ __('packages.currency_suffix') }}
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
                                    {{ number_format($finalPrice, 2) }}
                                    {{ __('packages.currency_suffix') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- الخدمات داخل الباقة --}}
            <div class="card">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            {{ __('packages.services_in_package') }}
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">
                            {{ __('packages.services_in_package_hint') }}
                        </span>
                    </h3>
                </div>

                <div class="card-body pt-0">

                    @if ($package->services->isEmpty())
                        <p class="text-muted mb-0">
                            {{ __('packages.no_services') }}
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed">
                                <thead>
                                    <tr class="fw-semibold fs-7 text-muted">
                                        <th>#</th>
                                        <th>{{ __('packages.service') }}</th>
                                        <th>{{ __('packages.category') }}</th>
                                        <th>{{ __('packages.duration') }}</th>
                                        <th>{{ __('packages.price') }}</th>
                                        <th>{{ __('packages.order_inside_package') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($package->services as $index => $service)
                                        @php
                                            $sName = $service->name[$locale]
                                                ?? (is_array($service->name) ? (reset($service->name) ?: '') : $service->name);

                                            $catName = null;
                                            if ($service->category) {
                                                $rawCatName = $service->category->name;
                                                if (is_array($rawCatName)) {
                                                    $catName = $rawCatName[$locale]
                                                        ?? (reset($rawCatName) ?: '');
                                                } else {
                                                    $catName = $rawCatName;
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $sName ?: __('services.singular_title') . ' #' . $service->id }}</td>
                                            <td>{{ $catName ?: '—' }}</td>
                                            <td>
                                                {{ $service->duration_minutes }}
                                                {{ __('services.minutes_suffix') }}
                                            </td>
                                            <td>
                                                {{ number_format($service->price, 2) }}
                                                {{ __('packages.currency_suffix') }}
                                            </td>
                                            <td>
                                                {{ $service->pivot?->sort_order ?? ($index + 1) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
@endsection
