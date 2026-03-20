<?php

namespace App\Infrastructure\Services\TelegramGateway;

use App\Application\TelegramGateway\DTOs\CheckSendAbilityData;
use App\Application\TelegramGateway\DTOs\CheckVerificationStatusData;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Application\TelegramGateway\DTOs\RevokeVerificationData;
use App\Application\TelegramGateway\DTOs\SendVerificationData;

interface TelegramGatewayInterface
{
    public function checkSendAbility(CheckSendAbilityData $data): RequestStatusData;

    public function sendVerificationMessage(SendVerificationData $data): RequestStatusData;

    public function checkVerificationStatus(CheckVerificationStatusData $data): RequestStatusData;

    public function revokeVerificationMessage(RevokeVerificationData $data): bool;
}
