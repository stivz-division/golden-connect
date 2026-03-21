<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $translationsPath = lang_path("{$locale}.json");
        $translations = file_exists($translationsPath)
            ? json_decode(file_get_contents($translationsPath), true) ?? []
            : [];

        $locales = collect(config('locales.available', []))->map(function ($localeData, $code) {
            return [
                'code' => $code,
                'name' => $localeData['name'],
                'name_en' => $localeData['name_en'],
                'flag' => $localeData['flag'],
                'short' => $localeData['short'],
            ];
        })->values()->all();

        return [
            ...parent::share($request),
            'locale' => $locale,
            'locales' => $locales,
            'translations' => $translations,
            'status' => fn () => $request->session()->get('status'),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ];
    }
}
