<?php

use App\Application\User\Actions\AuthViaTelegramAction;
use App\Application\User\DTOs\TelegramAuthData;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticates existing user by telegram_chat_id', function () {
    $user = User::factory()->create([
        'telegram_chat_id' => 123456789,
    ]);
    $user->saveAsRoot();

    $action = app(AuthViaTelegramAction::class);

    $result = $action->execute(new TelegramAuthData(
        telegramId: 123456789,
        firstName: 'Test',
    ));

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($user->id);
    expect(auth()->check())->toBeTrue();
    expect(auth()->id())->toBe($user->id);
});

it('returns null and stores session when user not found', function () {
    $action = app(AuthViaTelegramAction::class);

    $result = $action->execute(new TelegramAuthData(
        telegramId: 999999999,
        firstName: 'New',
        lastName: 'User',
        username: 'newuser',
        languageCode: 'en',
    ));

    expect($result)->toBeNull();
    expect(session('telegram_auth'))->not->toBeNull();
    expect(session('telegram_auth.telegram_id'))->toBe(999999999);
    expect(session('telegram_auth.first_name'))->toBe('New');
    expect(session('telegram_auth.username'))->toBe('newuser');
});
