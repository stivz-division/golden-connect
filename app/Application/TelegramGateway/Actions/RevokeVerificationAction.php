<?php

namespace App\Application\TelegramGateway\Actions;

use App\Application\TelegramGateway\DTOs\RevokeVerificationData;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

class RevokeVerificationAction
{
    public function __construct(
        private readonly TelegramGatewayInterface $gateway,
    ) {}

    public function execute(string $requestId): bool
    {
        return $this->gateway->revokeVerificationMessage(
            new RevokeVerificationData(request_id: $requestId),
        );
    }
}
