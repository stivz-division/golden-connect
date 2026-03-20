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

it('renders registration page', function () {
    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Register'));
});

it('sends verification code to phone', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register.send-code'), [
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

it('sends verification code to email', function () {
    Notification::fake();

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register.send-code'), [
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

it('registers user with phone and valid code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'phone' => '+79991234567',
    ]);
});

it('registers user with email and valid code', function () {
    VerificationCode::create([
        'identifier' => 'new@example.com',
        'type' => 'email',
        'code' => '654321',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'email',
            'identifier' => 'new@example.com',
            'code' => '654321',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'email' => 'new@example.com',
    ]);
});

it('rejects registration with invalid code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '000000',
        ]);

    $response->assertSessionHasErrors(['code']);
    $this->assertGuest();
});

it('rejects registration with expired code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $response->assertSessionHasErrors(['code']);
    $this->assertGuest();
});

it('creates user in nestedset tree as root when no users exist', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $user = User::where('phone', '+79991234567')->first();

    expect($user)->not->toBeNull();
    expect($user->parent_id)->toBeNull();
    expect($user->isRoot())->toBeTrue();
});

it('creates user as child of first user when no ref', function () {
    $root = User::factory()->create();
    $root->saveAsRoot();

    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $child = User::where('phone', '+79991234567')->first();
    $root->refresh();

    expect($child)->not->toBeNull();
    expect($child->parent_id)->toBe($root->id);
});

it('registers user with valid ref and attaches to mentor', function () {
    $mentor = User::factory()->create();
    $mentor->saveAsRoot();

    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
            'ref' => $mentor->uuid,
        ]);

    $this->assertAuthenticated();

    $newUser = User::where('phone', '+79991234567')->first();
    $mentor->refresh();

    expect($newUser)->not->toBeNull();
    expect($newUser->parent_id)->toBe($mentor->id);
});

it('requires type, identifier, and code for registration', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), []);

    $response->assertSessionHasErrors(['type', 'identifier', 'code']);
    $this->assertGuest();
});

it('redirects authenticated user from register page to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('register'));

    $response->assertRedirect(route('dashboard'));
});

it('passes ref query parameter as inertia prop on register page', function () {
    $testUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register', ['ref' => $testUuid]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register')
        ->where('ref', $testUuid)
        ->where('mentorUuid', $testUuid)
    );
});
