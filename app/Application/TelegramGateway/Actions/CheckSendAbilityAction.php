<?php

namespace App\Application\TelegramGateway\Actions;

use App\Application\TelegramGateway\DTOs\CheckSendAbilityData;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Domain\TelegramGateway\ValueObjects\PhoneNumber;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

class CheckSendAbilityAction
{
    public function __construct(
        private readonly TelegramGatewayInterface $gateway,
    ) {}

    public function execute(string $phoneNumber): RequestStatusData
    {
        $phone = new PhoneNumber($phoneNumber);

        return $this->gateway->checkSendAbility(
            new CheckSendAbilityData(phone_number: $phone->toString()),
        );
    }
}
