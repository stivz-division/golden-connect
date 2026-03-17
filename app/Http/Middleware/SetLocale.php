<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_keys(config('locales.available', []));

         if ($request->user() && in_array($request->user()->language, $availableLocales, true)) {
             $locale = $request->user()->language;
             session(['locale' => $locale]);
             app()->setLocale($locale);
             return $next($request);
         }

        if ($sessionLocale = session('locale')) {
            if (in_array($sessionLocale, $availableLocales, true)) {
                app()->setLocale($sessionLocale);

                return $next($request);
            }
        }

        if ($request->routeIs('locale.index', 'locale.store', 'locale.update', 'login.store', 'register.store')) {
            app()->setLocale(config('locales.default', 'ru'));

            return $next($request);
        }

        return redirect()->route('locale.index');
    }
}
