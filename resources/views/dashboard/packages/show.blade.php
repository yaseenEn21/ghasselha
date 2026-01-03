@extends('base.layout.app')

@section('content')

@section('top-btns')
    @can('packages.edit')
        <a href="{{ route('dashboard.packages.edit', $package->id) }}" class="btn btn-primary">
            تعديل الباقة
        </a>
    @endcan
@endsection

@php
    $locale = app()->getLocale();

    $name = $package->name[$locale] ?? (is_array($package->name) ? (reset($package->name) ?? '') : $package->name);
    $description = $package->description[$locale]
        ?? (is_array($package->description) ? (reset($package->description) ?? '') : $package->description);

    $totalSubscriptions = $package->subscriptions->count();                      // عدد الاشتراكات
    $uniqueCustomers = $package->subscriptions->pluck('user_id')->unique()->count(); // عدد المشتركين (عملاء مختلفين)
    $activeSubscriptions = $package->subscriptions->where('status', 'active')->count();
    $expiredSubscriptions = $package->subscriptions->where('status', 'expired')->count();
    $cancelledSubscriptions = $package->subscriptions->where('status', 'cancelled')->count();

    $finalPrice = $package->discounted_price ?: $package->price;
@endphp

<div class="row g-6 g-xl-9">

    {{-- العمود الجانبي: معلومات سريعة + إحصائيات --}}
    <div class="col-xl-4">

        {{-- بطاقة معلومات أساسية عن الباقة --}}
        <div class="card mb-6">
            <div class="card-body d-flex flex-column">

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
                            <h2 class="fw-bold mb-0">{{ $name }}</h2>

                            @if ($package->is_active)
                                <span class="badge badge-light-success">باقـة مفعّلة</span>
                            @else
                                <span class="badge badge-light-danger">غير مفعّلة</span>
                            @endif
                        </div>

                        @if ($description)
                            <div class="text-muted fw-semibold">
                                {{ Str::limit($description, 120) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="separator my-4"></div>

                {{-- تفاصيل رئيسية (سعر | مدة صلاحية | عدد الغسلات) --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">السعر الأساسي</span>
                        <span class="fw-bold">{{ number_format($package->price, 2) }} ر.س</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">السعر بعد الخصم</span>
                        <span class="fw-bold">
                            @if ($package->discounted_price)
                                {{ number_format($package->discounted_price, 2) }} ر.س
                            @else
                                —
                            @endif
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">مدة الصلاحية</span>
                        <span class="fw-bold">{{ $package->validity_days }} يوم</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">عدد الغسلات في الباقة</span>
                        <span class="fw-bold">{{ $package->washes_count }}</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">ترتيب الظهور</span>
                        <span class="fw-bold">#{{ $package->sort_order }}</span>
                    </div>
                </div>

                <div class="separator my-4"></div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">تاريخ الإنشاء</span>
                        <span class="fw-semibold">{{ optional($package->created_at)->format('Y-m-d') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">آخر تحديث</span>
                        <span class="fw-semibold">{{ optional($package->updated_at)->format('Y-m-d H:i') }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- كروت إحصائية صغيرة --}}
        <div class="row g-3">

            <div class="col-12">
                <div class="card card-flush h-100">
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
                                <div class="fs-6 text-muted">عدد المشتركين (عملاء مختلفين)</div>
                                <div class="fs-2 fw-bold">{{ $uniqueCustomers }}</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="text-muted fs-7">إجمالي الاشتراكات</div>
                            <div class="fw-semibold">{{ $totalSubscriptions }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- توزيع الحالات --}}
            <div class="col-12">
                <div class="card card-flush h-100">
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
                                <div class="fs-6 text-muted">حالة الاشتراكات</div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-success">نشطة</span>
                                <span class="fw-bold">{{ $activeSubscriptions }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-warning">منتهية</span>
                                <span class="fw-bold">{{ $expiredSubscriptions }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-danger">ملغاة</span>
                                <span class="fw-bold">{{ $cancelledSubscriptions }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- العمود الرئيسي: تفاصيل الباقة + الخدمات داخلها --}}
    <div class="col-xl-8">

        {{-- تفاصيل عامة بشكل جدول أنيق --}}
        <div class="card mb-6">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">تفاصيل الباقة</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">
                        نظرة عامة على خصائص الباقة وتسعيرها
                    </span>
                </h3>
            </div>

            <div class="card-body pt-0">
                <div class="row g-6">

                    <div class="col-md-6">
                        <div class="border rounded p-4 d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">الاسم (AR)</span>
                                <span class="fw-bold">
                                    {{ $package->name['ar'] ?? $name }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">الاسم (EN)</span>
                                <span class="fw-bold">
                                    {{ $package->name['en'] ?? '—' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">السعر النهائي المعروض</span>
                                <span class="fw-bold text-primary">
                                    {{ number_format($finalPrice, 2) }} ر.س
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-4 d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">عدد الغسلات</span>
                                <span class="fw-bold">{{ $package->washes_count }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">مدة الصلاحية</span>
                                <span class="fw-bold">{{ $package->validity_days }} يوم</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">الحالة</span>
                                <span class="fw-bold">
                                    @if ($package->is_active)
                                        <span class="badge badge-light-success">مفعّلة</span>
                                    @else
                                        <span class="badge badge-light-danger">غير مفعّلة</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($description)
                        <div class="col-12">
                            <div class="border rounded p-4">
                                <div class="fw-bold mb-2">وصف الباقة</div>
                                <p class="text-muted mb-0">
                                    {{ $description }}
                                </p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- الخدمات داخل الباقة --}}
        <div class="card">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">الخدمات ضمن الباقة</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">
                        ترتيب الخدمات كما ستظهر للعميل عند عرض تفاصيل الباقة
                    </span>
                </h3>
            </div>

            <div class="card-body pt-0">

                @if ($package->services->isEmpty())
                    <div class="alert alert-warning">
                        لا توجد خدمات مضافة لهذه الباقة حتى الآن.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-4">
                            <thead>
                            <tr class="fw-bold text-muted">
                                <th>#</th>
                                <th>الخدمة</th>
                                <th>التصنيف</th>
                                <th class="text-center">المدة</th>
                                <th class="text-center">السعر</th>
                                <th class="text-center">ترتيب داخل الباقة</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($package->services as $index => $service)
                                @php
                                    $serviceName = $service->name[$locale]
                                        ?? (is_array($service->name) ? (reset($service->name) ?? '') : $service->name);

                                    $catName = null;
                                    if ($service->category) {
                                        $catName = $service->category->name;
                                        if (is_array($catName)) {
                                            $catName = $catName[$locale] ?? (reset($catName) ?? '');
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $serviceName }}</span>
                                            @if (!empty($service->description))
                                                <span class="text-muted fs-7">
                                                    {{ Str::limit($service->description[$locale] ?? (reset($service->description) ?? ''), 80) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        {{ $catName ?: '—' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $service->duration_minutes }} دقيقة
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($service->price, 2) }} ر.س
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-primary">
                                            {{ $service->pivot->sort_order ?: ($index + 1) }}
                                        </span>
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