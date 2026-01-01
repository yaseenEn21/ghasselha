<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        
        if (!Auth::check() || Auth::user()->type_id !== 6) {
            
            $user = auth()->user();

            if (!$user || $user->type->key !== 'admin') {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'بيانات الدخول غير صحيحة.',
                ]);
            }
            
        }

        return $next($request);
    }

    
}
