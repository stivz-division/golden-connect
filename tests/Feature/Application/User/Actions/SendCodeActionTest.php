<?php

use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Application\User\Actions\SendCodeAction;
use App\Application\User\DTOs\SendCodeData;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Models\VerificationCode;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('send-code:+79991234567');
    RateLimiter::clear('send-code:test@example.com');
});

it('creates verification code and sends via phone', function () {
    $this->mock(TelegramGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('sendVerificationMessage')
            ->once()
            ->andReturn(new RequestStatusData(
                request_id: 'test',
                phone_number: '+79991234567',
                request_cost: 0,
            ));
    });

    $action = app(SendCodeAction::class);

    $action->execute(new SendCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
    ));

    $code = VerificationCode::where('identifier', '+79991234567')->first();

    expect($code)->not->toBeNull();
    expect($code->type)->toBe(ContactType::Phone);
    expect($code->code)->toHaveLength(6);
    expect($code->expires_at)->toBeGreaterThan(now());
});

it('creates verification code and sends via email', function () {
    Notification::fake();

    $this->mock(TelegramGatewayInterface::class);

    $action = app(SendCodeAction::class);

    $action->execute(new SendCodeData(
        identifier: 'test@example.com',
        type: ContactType::Email,
    ));

    $code = VerificationCode::where('identifier', 'test@example.com')->first();

    expect($code)->not->toBeNull();
    expect($code->type)->toBe(ContactType::Email);
});

it('rate limits code sending to 60 seconds', function () {
    $this->mock(TelegramGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('sendVerificationMessage')
            ->once()
            ->andReturn(new RequestStatusData(
                request_id: 'test',
                phone_number: '+79991234567',
                request_cost: 0,
            ));
    });

    $action = app(SendCodeAction::class);

    $action->execute(new SendCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
    ));

    $action->execute(new SendCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
    ));
})->throws(ValidationException::class);
