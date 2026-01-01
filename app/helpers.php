<?php

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
