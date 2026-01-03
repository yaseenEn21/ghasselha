{{-- resources/views/dashboard/package_subscriptions/edit.blade.php --}}
@extends('base.layout.app')

@section('content')

    @section('top-btns')
        @can('package_subscriptions.view')
            <a href="{{ route('dashboard.package-subscriptions.show', $subscription->id) }}" class="btn btn-secondary">
                {{ __('package_subscriptions.view') }}
            </a>
        @endcan
    @endsection

    @php
        $locale = app()->getLocale();
        $user    = $subscription->user;
        $package = $subscription->package;

        $packageName = null;
        if ($package) {
            $rawName = $package->name;
            if (is_array($rawName)) {
                $packageName = $rawName[$locale] ?? (reset($rawName) ?: '');
            } else {
                $packageName = $rawName;
            }
        }

        $startsAtOld = old('starts_at', optional($subscription->starts_at)->format('Y-m-d'));
        $endsAtOld   = old('ends_at', optional($subscription->ends_at)->format('Y-m-d'));
        $statusOld   = old('status', $subscription->status);
        $remainingOld = old('remaining_washes', $subscription->remaining_washes);
        $purchasedOld = old('purchased_at', optional($subscription->purchased_at)->format('Y-m-d\TH:i'));
    @endphp

    <div class="card">
        <form id="package_subscription_edit_form"
              action="{{ route('dashboard.package-subscriptions.update', $subscription->id) }}"
              method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">

                <div id="form_result" class="alert d-none"></div>

                <div class="row g-9">
                    {{-- معلومات ثابتة عن العميل والباقة --}}
                    <div class="col-lg-4">
                        <div class="card card-flush h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-4 mb-1">
                                        {{ __('package_subscriptions.singular_title') }} #{{ $subscription->id }}
                                    </span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">

                                <div class="mb-4">
                                    <div class="text-muted fw-semibold mb-1">
                                        {{ __('package_subscriptions.package') }}
                                    </div>
                                    <div class="fw-bold">
                                        {{ $packageName ?: '—' }}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="text-muted fw-semibold mb-1">
                                        {{ __('package_subscriptions.customer') }}
                                    </div>
                                    <div class="fw-bold">
                                        {{ $user?->name ?? '—' }}
                                    </div>
                                    @if($user?->mobile)
                                        <div class="text-muted fs-7">{{ $user->mobile }}</div>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <div class="text-muted fw-semibold mb-1">
                                        {{ __('package_subscriptions.final_price') }}
                                    </div>
                                    <div class="fw-bold">
                                        {{ number_format($subscription->final_price_snapshot, 2) }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-muted fw-semibold mb-1">
                                        {{ __('package_subscriptions.remaining_washes') }}
                                    </div>
                                    <div class="fw-bold">
                                        {{ $subscription->remaining_washes }} / {{ $subscription->total_washes_snapshot }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- فورم التعديل --}}
                    <div class="col-lg-8">
                        <div class="card card-flush h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-4 mb-1">
                                        {{ __('package_subscriptions.edit') }}
                                    </span>
                                </h3>
                            </div>

                            <div class="card-body pt-0">

                                <div class="row g-6">

                                    {{-- الحالة --}}
                                    <div class="col-md-4 fv-row">
                                        <label class="required fw-semibold fs-6 mb-2">
                                            {{ __('package_subscriptions.status') }}
                                        </label>
                                        <select name="status" class="form-select">
                                            <option value="active" {{ $statusOld === 'active' ? 'selected' : '' }}>
                                                {{ __('package_subscriptions.status_active') }}
                                            </option>
                                            <option value="expired" {{ $statusOld === 'expired' ? 'selected' : '' }}>
                                                {{ __('package_subscriptions.status_expired') }}
                                            </option>
                                            <option value="cancelled" {{ $statusOld === 'cancelled' ? 'selected' : '' }}>
                                                {{ __('package_subscriptions.status_cancelled') }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- تاريخ البداية --}}
                                    <div class="col-md-4 fv-row">
                                        <label class="required fw-semibold fs-6 mb-2">
                                            {{ __('package_subscriptions.filters.starts_from') }}
                                        </label>
                                        <input type="date"
                                               name="starts_at"
                                               class="form-control"
                                               value="{{ $startsAtOld }}">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- تاريخ النهاية --}}
                                    <div class="col-md-4 fv-row">
                                        <label class="required fw-semibold fs-6 mb-2">
                                            {{ __('package_subscriptions.filters.ends_to') }}
                                        </label>
                                        <input type="date"
                                               name="ends_at"
                                               class="form-control"
                                               value="{{ $endsAtOld }}">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- الغسلات المتبقية --}}
                                    <div class="col-md-4 fv-row">
                                        <label class="required fw-semibold fs-6 mb-2">
                                            {{ __('package_subscriptions.remaining_washes') }}
                                        </label>
                                        <input type="number"
                                               min="0"
                                               max="{{ $subscription->total_washes_snapshot }}"
                                               name="remaining_washes"
                                               class="form-control"
                                               value="{{ $remainingOld }}">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- تاريخ الشراء --}}
                                    <div class="col-md-4 fv-row">
                                        <label class="fw-semibold fs-6 mb-2">
                                            {{ __('package_subscriptions.purchased_at') }}
                                        </label>
                                        <input type="datetime-local"
                                               name="purchased_at"
                                               class="form-control"
                                               value="{{ $purchasedOld }}">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ __('package_subscriptions.edit') }}</span>
                </button>
            </div>

        </form>
    </div>
@endsection

@push('custom-script')
    <script>
        (function () {
            const $form = $('#package_subscription_edit_form');

            $form.on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData($form[0]);
                // تأكيد وجود _method=PUT للـ AJAX
                formData.set('_method', 'PUT');

                if (window.KH && typeof window.KH.setFormLoading === 'function') {
                    window.KH.setFormLoading($form, true, {text: '{{ app()->getLocale() === "ar" ? "جاري الحفظ..." : "Saving..." }}'});
                }

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ app()->getLocale() === "ar" ? "تم" : "Done" }}',
                            text: res.message || '{{ app()->getLocale() === "ar" ? "تم تحديث الاشتراك بنجاح." : "Subscription updated successfully." }}',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        if (res.redirect) {
                            window.location.href = res.redirect;
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                                window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                                    globalAlertSelector: '#form_result'
                                });
                            }
                        } else {
                            let msg = '{{ app()->getLocale() === "ar" ? "حدث خطأ غير متوقع." : "Unexpected error occurred." }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire(
                                '{{ app()->getLocale() === "ar" ? "خطأ" : "Error" }}',
                                msg,
                                'error'
                            );
                        }
                    },
                    complete: function () {
                        if (window.KH && typeof window.KH.setFormLoading === 'function') {
                            window.KH.setFormLoading($form, false);
                        }
                    }
                });
            });
        })();
    </script>
@endpush