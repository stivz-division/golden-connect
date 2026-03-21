<?php

use App\Application\User\Actions\LinkTelegramAction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links telegram_chat_id to user from session', function () {
    $user = User::factory()->create();
    $user->saveAsRoot();

    session()->put('telegram_auth', [
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser',
        'language_code' => 'ru',
    ]);

    $action = app(LinkTelegramAction::class);
    $action->execute($user);

    $user->refresh();
    expect($user->telegram_chat_id)->toBe(123456789);
    expect(session('telegram_auth'))->toBeNull();
});

it('does nothing when no telegram data in session', function () {
    $user = User::factory()->create();
    $user->saveAsRoot();

    $action = app(LinkTelegramAction::class);
    $action->execute($user);

    $user->refresh();
    expect($user->telegram_chat_id)->toBeNull();
});
