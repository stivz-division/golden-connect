<?php

use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Domain\User\Models\User;
use App\Domain\User\Models\VerificationCode;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('send-code:+79991234567');
    RateLimiter::clear('send-code:test@example.com');

    $this->mock(TelegramGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('sendVerificationMessage')->andReturn(
            new RequestStatusData(
                request_id: 'test-request-id',
                phone_number: '+79991234567',
                request_cost: 0,
            )
        );
    });
});

it('renders login page', function () {
    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('login'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
});

it('sends verification code to phone for login', function () {
    User::factory()->create(['phone' => '+79991234567']);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login.send-code'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('verification_codes', [
        'identifier' => '+79991234567',
        'type' => 'phone',
    ]);
});

it('sends verification code to email for login', function () {
    Notification::fake();
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login.send-code'), [
            'type' => 'email',
            'identifier' => 'test@example.com',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('verification_codes', [
        'identifier' => 'test@example.com',
        'type' => 'email',
    ]);
});

it('does not send code for nonexistent user but returns success', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login.send-code'), [
            'type' => 'phone',
            'identifier' => '+79999999999',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('verification_codes', [
        'identifier' => '+79999999999',
    ]);
});

it('authenticates user with phone and valid code', function () {
    User::factory()->create(['phone' => '+79991234567']);

    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

it('authenticates user with email and valid code', function () {
    User::factory()->create(['email' => 'test@example.com']);

    VerificationCode::create([
        'identifier' => 'test@example.com',
        'type' => 'email',
        'code' => '654321',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'type' => 'email',
            'identifier' => 'test@example.com',
            'code' => '654321',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

it('rejects login with invalid code', function () {
    User::factory()->create(['phone' => '+79991234567']);

    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '000000',
        ]);

    $response->assertSessionHasErrors(['code']);
    $this->assertGuest();
});

it('rejects login when user not found', function () {
    VerificationCode::create([
        'identifier' => '+79999999999',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'type' => 'phone',
            'identifier' => '+79999999999',
            'code' => '123456',
        ]);

    $response->assertSessionHasErrors(['identifier']);
    $this->assertGuest();
});

it('rejects login with expired code', function () {
    User::factory()->create(['phone' => '+79991234567']);

    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $response->assertSessionHasErrors(['code']);
    $this->assertGuest();
});

it('requires type, identifier, and code for login', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('login'), []);

    $response->assertSessionHasErrors(['type', 'identifier', 'code']);
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
