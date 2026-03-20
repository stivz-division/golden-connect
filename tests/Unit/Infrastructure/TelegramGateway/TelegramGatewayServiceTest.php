<?php

use Tests\TestCase;

uses(TestCase::class);

use App\Application\TelegramGateway\DTOs\CheckSendAbilityData;
use App\Application\TelegramGateway\DTOs\CheckVerificationStatusData;
use App\Application\TelegramGateway\DTOs\RevokeVerificationData;
use App\Application\TelegramGateway\DTOs\SendVerificationData;
use App\Domain\TelegramGateway\Enums\DeliveryStatus;
use App\Domain\TelegramGateway\Enums\VerificationStatus;
use App\Domain\TelegramGateway\Exceptions\InvalidTokenException;
use App\Domain\TelegramGateway\Exceptions\TelegramGatewayException;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.telegram_gateway.token' => 'test-token',
        'services.telegram_gateway.base_url' => 'https://gatewayapi.telegram.org',
    ]);
});

it('checks send ability successfully', function () {
    Http::fake([
        'gatewayapi.telegram.org/checkSendAbility' => Http::response([
            'ok' => true,
            'result' => [
                'request_id' => 'req_123',
                'phone_number' => '+79991234567',
                'request_cost' => 0.0,
            ],
        ]),
    ]);

    $service = new TelegramGatewayService;
    $result = $service->checkSendAbility(new CheckSendAbilityData(phone_number: '+79991234567'));

    expect($result->request_id)->toBe('req_123');
    expect($result->phone_number)->toBe('+79991234567');
    expect($result->request_cost)->toBe(0.0);
});

it('sends verification message successfully', function () {
    Http::fake([
        'gatewayapi.telegram.org/sendVerificationMessage' => Http::response([
            'ok' => true,
            'result' => [
                'request_id' => 'req_456',
                'phone_number' => '+79991234567',
                'request_cost' => 0.5,
                'remaining_balance' => 9.5,
                'delivery_status' => [
                    'status' => 'sent',
                    'updated_at' => 1700000000,
                ],
            ],
        ]),
    ]);

    $service = new TelegramGatewayService;
    $result = $service->sendVerificationMessage(new SendVerificationData(
        phone_number: '+79991234567',
        code: '1234',
        code_length: 4,
    ));

    expect($result->request_id)->toBe('req_456');
    expect($result->request_cost)->toBe(0.5);
    expect($result->remaining_balance)->toBe(9.5);
    expect($result->delivery_status->status)->toBe(DeliveryStatus::Sent);
});

it('checks verification status successfully', function () {
    Http::fake([
        'gatewayapi.telegram.org/checkVerificationStatus' => Http::response([
            'ok' => true,
            'result' => [
                'request_id' => 'req_456',
                'phone_number' => '+79991234567',
                'request_cost' => 0.0,
                'verification_status' => [
                    'status' => 'code_valid',
                    'updated_at' => 1700000001,
                    'code_entered' => '1234',
                ],
            ],
        ]),
    ]);

    $service = new TelegramGatewayService;
    $result = $service->checkVerificationStatus(new CheckVerificationStatusData(
        request_id: 'req_456',
        code: '1234',
    ));

    expect($result->verification_status->status)->toBe(VerificationStatus::CodeValid);
    expect($result->verification_status->code_entered)->toBe('1234');
});

it('revokes verification message successfully', function () {
    Http::fake([
        'gatewayapi.telegram.org/revokeVerificationMessage' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    $service = new TelegramGatewayService;
    $result = $service->revokeVerificationMessage(new RevokeVerificationData(request_id: 'req_456'));

    expect($result)->toBeTrue();
});

it('throws InvalidTokenException on 401', function () {
    Http::fake([
        'gatewayapi.telegram.org/checkSendAbility' => Http::response([
            'ok' => false,
            'error' => 'Unauthorized',
        ], 401),
    ]);

    $service = new TelegramGatewayService;
    $service->checkSendAbility(new CheckSendAbilityData(phone_number: '+79991234567'));
})->throws(InvalidTokenException::class);

it('throws TelegramGatewayException on generic error', function () {
    Http::fake([
        'gatewayapi.telegram.org/checkSendAbility' => Http::response([
            'ok' => false,
            'error' => 'Some unknown error',
        ], 500),
    ]);

    $service = new TelegramGatewayService;
    $service->checkSendAbility(new CheckSendAbilityData(phone_number: '+79991234567'));
})->throws(TelegramGatewayException::class);
