<?php

namespace App\Application\TelegramGateway\Actions;

use App\Application\TelegramGateway\DTOs\CheckVerificationStatusData;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

class CheckVerificationStatusAction
{
    public function __construct(
        private readonly TelegramGatewayInterface $gateway,
    ) {}

    public function execute(CheckVerificationStatusData $data): RequestStatusData
    {
        return $this->gateway->checkVerificationStatus($data);
    }
}
