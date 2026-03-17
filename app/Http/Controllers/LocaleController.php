<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocaleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LocaleController extends Controller
{
    public function index(): Response
    {
        $locales = collect(config('locales.available', []))->map(function ($locale, $code) {
            return [
                'code' => $code,
                'name' => $locale['name'],
                'name_en' => $locale['name_en'],
                'flag' => $locale['flag'],
                'short' => $locale['short'],
            ];
        })->values()->all();

        return Inertia::render('Language', [
            'locales' => $locales,
            'currentLocale' => session('locale', config('locales.default', 'ru')),
        ]);
    }

    public function update(StoreLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return redirect()->route('locale.index');
    }

    public function store(StoreLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');

        session(['locale' => $locale]);
        app()->setLocale($locale);

        Log::debug('LocaleController: locale confirmed', ['locale' => $locale]);

        // TODO: раскомментировать когда у User появится поле language
        // if ($request->user()) {
        //     $request->user()->update(['language' => $locale]);
        // }

        return redirect('/');
    }
}
