<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetApiLocale
{
    public function handle(Request $request, Closure $next)
    {
        $lang = request_lang(['ar','en'], 'ar');
        app()->setLocale($lang);

        return $next($request);
    }
}