<?php

use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Domain\User\Models\User;
use App\Domain\User\Models\VerificationCode;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Micromagicman\TelegramWebApp\Dto\TelegramUser;
use Micromagicman\TelegramWebApp\Http\WebAppDataValidationMiddleware;
use Micromagicman\TelegramWebApp\Service\TelegramWebAppService;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('send-code:+79991234567');

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

it('renders telegram load page', function () {
    $response = $this->get(route('telegram.load'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/TelegramLoad'));
});

it('authenticates existing user via telegram', function () {
    $user = User::factory()->create([
        'telegram_chat_id' => 123456789,
    ]);
    $user->saveAsRoot();

    $this->mock(TelegramWebAppService::class, function ($mock) {
        $mock->shouldReceive('getWebAppUser')->once()->andReturn(
            new TelegramUser([
                'id' => 123456789,
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'testuser',
                'language_code' => 'ru',
            ])
        );
    });

    $response = $this
        ->withoutMiddleware(WebAppDataValidationMiddleware::class)
        ->get(route('telegram.auth', [
            'user' => json_encode(['id' => 123456789]),
            'auth_date' => time(),
            'hash' => 'fakehash',
        ]));

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
});

it('redirects to register when telegram user not found', function () {
    $this->mock(TelegramWebAppService::class, function ($mock) {
        $mock->shouldReceive('getWebAppUser')->once()->andReturn(
            new TelegramUser([
                'id' => 999999999,
                'first_name' => 'New',
                'last_name' => 'User',
                'username' => 'newuser',
                'language_code' => 'en',
            ])
        );
    });

    $response = $this
        ->withoutMiddleware(WebAppDataValidationMiddleware::class)
        ->get(route('telegram.auth', [
            'user' => json_encode(['id' => 999999999]),
            'auth_date' => time(),
            'hash' => 'fakehash',
        ]));

    $this->assertGuest();
    $response->assertRedirect(route('register'));
    $response->assertSessionHas('telegram_linked', true);
    $response->assertSessionHas('telegram_auth.telegram_id', 999999999);
});

it('stores telegram data in session when user not found', function () {
    $this->mock(TelegramWebAppService::class, function ($mock) {
        $mock->shouldReceive('getWebAppUser')->once()->andReturn(
            new TelegramUser([
                'id' => 999999999,
                'first_name' => 'New',
                'username' => 'newuser',
                'language_code' => 'en',
            ])
        );
    });

    $response = $this
        ->withoutMiddleware(WebAppDataValidationMiddleware::class)
        ->get(route('telegram.auth', [
            'user' => json_encode(['id' => 999999999]),
            'auth_date' => time(),
            'hash' => 'fakehash',
        ]));

    $response->assertSessionHas('telegram_auth.telegram_id', 999999999);
    $response->assertSessionHas('telegram_auth.first_name', 'New');
    $response->assertSessionHas('telegram_auth.username', 'newuser');
});

it('links telegram after registration when session has telegram data', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession([
            'locale' => 'ru',
            'telegram_auth' => [
                'telegram_id' => 777888999,
                'first_name' => 'Tg',
                'last_name' => 'User',
                'username' => 'tguser',
                'language_code' => 'ru',
            ],
        ])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79991234567',
            'code' => '123456',
        ]);

    $this->assertAuthenticated();

    $user = User::where('phone', '+79991234567')->first();
    expect($user->telegram_chat_id)->toBe(777888999);
});

it('does not link telegram when session has no telegram data', function () {
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
    expect($user->telegram_chat_id)->toBeNull();
});

it('rejects request when user parameter is missing', function () {
    $response = $this
        ->withoutMiddleware(WebAppDataValidationMiddleware::class)
        ->get(route('telegram.auth', [
            'auth_date' => time(),
            'hash' => 'fakehash',
        ]));

    $response->assertSessionHasErrors(['user']);
});

it('redirects to login when telegram user data is invalid', function () {
    $this->mock(TelegramWebAppService::class, function ($mock) {
        $mock->shouldReceive('getWebAppUser')->once()->andReturnNull();
    });

    $response = $this
        ->withoutMiddleware(WebAppDataValidationMiddleware::class)
        ->get(route('telegram.auth', [
            'user' => 'invalid-json',
            'auth_date' => time(),
            'hash' => 'fakehash',
        ]));

    $response->assertRedirect(route('login'));
});

it('passes telegramLinked prop to register page when flash is set', function () {
    $response = $this
        ->withSession(['locale' => 'ru', 'telegram_linked' => true])
        ->get(route('register'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register')
        ->where('telegramLinked', true)
    );
});
