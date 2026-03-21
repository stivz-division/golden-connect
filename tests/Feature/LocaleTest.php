<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

it('redirects guest without locale to language selection', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('locale.index'));
});

it('redirects guest with locale to dashboard', function () {
    $response = $this->withSession(['locale' => 'ru'])->get('/');

    $response->assertRedirect('/dashboard');
});

it('updates session locale on valid POST', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post(route('locale.store'), ['locale' => 'en']);

    $response->assertRedirect('/login');
    $response->assertSessionHas('locale', 'en');
});

it('returns validation error on invalid locale POST', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->from(route('locale.index'))
        ->post(route('locale.store'), ['locale' => 'invalid']);

    $response->assertSessionHasErrors('locale');
});

it('switches locale via PATCH and redirects back to language page', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->patch(route('locale.update'), ['locale' => 'en']);

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'en');
});

it('returns validation error on invalid locale PATCH', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->from(route('locale.index'))
        ->patch(route('locale.update'), ['locale' => 'xyz']);

    $response->assertSessionHasErrors('locale');
});

it('renders language page without redirect loop', function () {
    $response = $this->get(route('locale.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Language'));
});

it('has correct config structure for available locales', function () {
    $locales = config('locales.available');

    expect($locales)->toBeArray()
        ->and($locales)->toHaveKeys(['ru', 'en']);

    foreach ($locales as $code => $locale) {
        expect($locale)->toHaveKeys(['name', 'name_en', 'flag', 'short']);
    }
});
