<?php

use App\Application\TelegramGateway\Actions\SendVerificationAction;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Application\TelegramGateway\DTOs\SendVerificationData;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

it('validates input and calls gateway', function () {
    $expectedResult = new RequestStatusData(
        request_id: 'req_456',
        phone_number: '+79991234567',
        request_cost: 0.5,
    );

    $gateway = Mockery::mock(TelegramGatewayInterface::class);
    $gateway->shouldReceive('sendVerificationMessage')
        ->once()
        ->andReturn($expectedResult);

    $action = new SendVerificationAction($gateway);
    $data = new SendVerificationData(
        phone_number: '+79991234567',
        code: '1234',
    );
    $result = $action->execute($data);

    expect($result->request_id)->toBe('req_456');
});

it('rejects invalid phone number', function () {
    $gateway = Mockery::mock(TelegramGatewayInterface::class);

    $action = new SendVerificationAction($gateway);
    $data = new SendVerificationData(phone_number: 'invalid');
    $action->execute($data);
})->throws(InvalidArgumentException::class);

it('rejects invalid verification code', function () {
    $gateway = Mockery::mock(TelegramGatewayInterface::class);

    $action = new SendVerificationAction($gateway);
    $data = new SendVerificationData(
        phone_number: '+79991234567',
        code: '12', // too short
    );
    $action->execute($data);
})->throws(InvalidArgumentException::class);
