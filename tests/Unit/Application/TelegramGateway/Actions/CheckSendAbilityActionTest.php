<?php

use App\Application\TelegramGateway\Actions\CheckSendAbilityAction;
use App\Application\TelegramGateway\DTOs\CheckSendAbilityData;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

it('validates phone number and calls gateway', function () {
    $expectedResult = new RequestStatusData(
        request_id: 'req_123',
        phone_number: '+79991234567',
        request_cost: 0.0,
    );

    $gateway = Mockery::mock(TelegramGatewayInterface::class);
    $gateway->shouldReceive('checkSendAbility')
        ->once()
        ->withArgs(fn (CheckSendAbilityData $data) => $data->phone_number === '+79991234567')
        ->andReturn($expectedResult);

    $action = new CheckSendAbilityAction($gateway);
    $result = $action->execute('+79991234567');

    expect($result->request_id)->toBe('req_123');
});

it('rejects invalid phone number', function () {
    $gateway = Mockery::mock(TelegramGatewayInterface::class);

    $action = new CheckSendAbilityAction($gateway);
    $action->execute('invalid');
})->throws(InvalidArgumentException::class);
