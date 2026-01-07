<?php

use Illuminate\Database\Eloquent\Model;

if (!function_exists('t')) {
    /**
     * ترجمة مع افتراض ملف messages إذا لم يحدد
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function t($key, $replace = [], $locale = null) {
        if (!str_contains($key, '.')) {
            $key = "messages.$key";
        }
        return __($key, $replace, $locale);
    }

    function defaultImage($name = 'service.png') {
        return asset('assets/media/avatars/' . $name);
    }
}

if (! function_exists('invoiceable_label')) {

    /**
     * @return array{label:string,id:int,route:?string}
     */
    function invoiceable_label(string $short, ?int $invoiceableId, ? Model $invoiceable = null): array
    {
        $id = (int) ($invoiceableId ?? 0);

        if ($id <= 0 || $short === '') {
            return ['label' => '—', 'id' => $id, 'route' => null];
        }

        $label = '';
        $route = null;

        if ($short === 'Package') {
            $pkg = $invoiceable instanceof \App\Models\Package
                ? $invoiceable
                : \App\Models\Package::query()->select(['id', 'name'])->find($id);

            $label = translatable_value($pkg, 'name') ?: __('invoices.Package');

            $route = \Illuminate\Support\Facades\Route::has('dashboard.packages.show')
                ? route('dashboard.packages.show', $id)
                : null;

            return ['label' => $label, 'id' => $id, 'route' => $route];
        }

        if ($short === 'PackageSubscription') {
            $sub = $invoiceable instanceof \App\Models\PackageSubscription
                ? $invoiceable
                : \App\Models\PackageSubscription::query()->select(['id', 'name'])->find($id);

            $label = translatable_value($sub, 'name') ?: __('invoices.PackageSubscription');

            $route = \Illuminate\Support\Facades\Route::has('dashboard.package_subscriptions.show')
                ? route('dashboard.package_subscriptions.show', $id)
                : null;

            return ['label' => $label, 'id' => $id, 'route' => $route];
        }

        if ($short === 'Booking') {
            $booking = $invoiceable instanceof \App\Models\Booking
                ? $invoiceable
                : \App\Models\Booking::query()->select(['id', 'code', 'reference', 'booking_no'])->find($id);

            $number = $booking?->code
                ?? $booking?->reference
                ?? $booking?->booking_no
                ?? ('#' . $id);

            $label = __('invoices.Booking') . ' ' . $number;

            $route = \Illuminate\Support\Facades\Route::has('dashboard.bookings.show')
                ? route('dashboard.bookings.show', $id)
                : null;

            return ['label' => $label, 'id' => $id, 'route' => $route];
        }

        // fallback
        $translated = __('invoices.' . $short);
        $label = ($translated !== 'invoices.' . $short ? $translated : $short) . ' #' . $id;

        return ['label' => $label, 'id' => $id, 'route' => null];
    }
}

if (! function_exists('translatable_value')) {

    function translatable_value(?\Illuminate\Database\Eloquent\Model $model, string $field): string
    {
        if (! $model) return '';

        if (method_exists($model, 'getTranslation')) {
            return (string) $model->getTranslation($field, app()->getLocale());
        }

        $val = $model->{$field} ?? '';

        if (is_array($val)) {
            return (string) ($val[app()->getLocale()] ?? $val['en'] ?? (count($val) ? reset($val) : '') ?? '');
        }

        if (is_string($val)) {
            $trim = trim($val);

            if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
                $decoded = json_decode($trim, true);
                if (is_array($decoded)) {
                    return (string) ($decoded[app()->getLocale()] ?? $decoded['en'] ?? (count($decoded) ? reset($decoded) : '') ?? '');
                }
            }
        }

        return (string) $val;
    }
}

