<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders login page', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
});

it('authenticates user with valid credentials and redirects to dashboard', function () {
    User::factory()->create([
        'login' => 'testuser',
        'password' => 'secret123',
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'login' => 'testuser',
            'password' => 'secret123',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

it('does not authenticate with invalid password', function () {
    User::factory()->create([
        'login' => 'testuser',
        'password' => 'secret123',
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'login' => 'testuser',
            'password' => 'wrong-password',
        ]);

    $this->assertGuest();
});

it('requires login and password fields', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'login' => '',
            'password' => '',
        ]);

    $response->assertSessionHasErrors(['login']);
    $this->assertGuest();
});

it('redirects authenticated user from login page to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('dashboard'));
});

it('requires authentication for dashboard', function () {
    $response = $this->withSession(['locale' => 'ru'])->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});
