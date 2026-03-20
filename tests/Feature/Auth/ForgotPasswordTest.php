<?php

use App\Domain\User\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('renders forgot password page', function () {
    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('password.request'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));
});

it('sends password reset link to valid email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('password.email'), [
            'email' => 'test@example.com',
        ]);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('does not send reset link for invalid email', function () {
    Notification::fake();

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

    Notification::assertNothingSent();
});

it('validates email is required for password reset', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('password.email'), [
            'email' => '',
        ]);

    $response->assertSessionHasErrors(['email']);
});

it('renders reset password page with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('password.reset', [
            'token' => $token,
            'email' => 'test@example.com',
        ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/ResetPassword')
        ->has('token')
        ->has('email')
    );
});

it('resets password with valid data', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertSessionHasNoErrors();
});
