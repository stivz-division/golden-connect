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

        // TODO: раскомментировать когда у User появится поле language
        // if ($request->user() && in_array($request->user()->language, $availableLocales, true)) {
        //     $locale = $request->user()->language;
        //     session(['locale' => $locale]);
        //     app()->setLocale($locale);
        //     Log::debug('SetLocale: locale from user profile', ['locale' => $locale]);
        //     return $next($request);
        // }

        if ($sessionLocale = session('locale')) {
            if (in_array($sessionLocale, $availableLocales, true)) {
                app()->setLocale($sessionLocale);

                return $next($request);
            }
        }

        if ($request->routeIs('locale.index', 'locale.store', 'locale.update')) {
            app()->setLocale(config('locales.default', 'ru'));

            return $next($request);
        }

        return redirect()->route('locale.index');
    }
}
