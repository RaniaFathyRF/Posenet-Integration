<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->get('lang', config('app.locale')); 
        if (in_array($locale, ['en', 'ar'])) { 
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
