<?php

use App\Application\TelegramGateway\Actions\RevokeVerificationAction;
use App\Application\TelegramGateway\DTOs\RevokeVerificationData;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

it('calls gateway to revoke verification', function () {
    $gateway = Mockery::mock(TelegramGatewayInterface::class);
    $gateway->shouldReceive('revokeVerificationMessage')
        ->once()
        ->withArgs(fn (RevokeVerificationData $data) => $data->request_id === 'req_789')
        ->andReturn(true);

    $action = new RevokeVerificationAction($gateway);
    $result = $action->execute('req_789');

    expect($result)->toBeTrue();
});
