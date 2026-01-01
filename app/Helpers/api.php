<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

if (!function_exists('api_success')) {
    /**
     * نجاح عادي
     */
    function api_success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

if (!function_exists('api_error')) {
    /**
     * خطأ عام
     */
    function api_error(
        string $message = 'Something went wrong',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}

if (!function_exists('api_validation_error')) {
    /**
     * خطأ Valdiation
     */
    function api_validation_error(
        mixed $errors,
        string $message = 'Validation error'
    ): JsonResponse {
        return api_error($message, 422, $errors);
    }
}

if (!function_exists('api_paginated')) {
    /**
     * ريسبونس للنتائج مع pagination
     */
    function api_paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Success'
    ): JsonResponse {
        return api_success([
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], $message);
    }

    if (!function_exists('request_lang')) {
        function request_lang(array $allowed = ['ar', 'en'], string $default = 'ar'): string
        {
            $q = request()->query('lang');
            if ($q && in_array($q, $allowed, true))
                return $q;

            $header = request()->header('Accept-Language', '');
            $lang = strtolower(trim(explode(',', $header)[0] ?? ''));
            $lang = explode('-', $lang)[0] ?? $lang; // ar-SA => ar

            return in_array($lang, $allowed, true) ? $lang : $default;
        }
    }

    if (!function_exists('i18n')) {
        function i18n($value, ?string $lang = null, string $fallback = 'en'): ?string
        {
            if (is_null($value))
                return null;

            // لو جاي string قديم
            if (is_string($value))
                return $value;

            // لو جاي array/json
            if (is_array($value)) {
                $lang = $lang ?: request_lang();

                if (!empty($value[$lang]))
                    return (string) $value[$lang];
                if (!empty($value[$fallback]))
                    return (string) $value[$fallback];

                // أول قيمة غير فاضية
                foreach ($value as $v) {
                    if (!empty($v))
                        return (string) $v;
                }
            }

            return null;
        }
    }

    if (!function_exists('car_color_map')) {
        function car_color_map(): array
        {
            return [
                'red' => '#FF0000',
                'silver' => '#C0C0C0',
                'white' => '#FFFFFF',
                'black' => '#000000',

                'brown' => '#8B4513',
                'orange' => '#FFA500',
                'purple' => '#800080',
                'gold' => '#FFD700',

                'green' => '#00A000',
                'blue' => '#1E90FF',
                'yellow' => '#FFD400',
                'beige' => '#F5DEB3',
            ];
        }
    }

    if (!function_exists('car_color_hex')) {
        function car_color_hex(?string $key): ?string
        {
            if (!$key)
                return null;
            $map = car_color_map();
            return $map[$key] ?? null;
        }
    }

}
