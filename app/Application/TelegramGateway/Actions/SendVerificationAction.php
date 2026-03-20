<?php

namespace App\Application\TelegramGateway\Actions;

use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Application\TelegramGateway\DTOs\SendVerificationData;
use App\Domain\TelegramGateway\ValueObjects\PhoneNumber;
use App\Domain\TelegramGateway\ValueObjects\VerificationCode;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;

class SendVerificationAction
{
    public function __construct(
        private readonly TelegramGatewayInterface $gateway,
    ) {}

    public function execute(SendVerificationData $data): RequestStatusData
    {
        new PhoneNumber($data->phone_number);

        if ($data->code !== null) {
            new VerificationCode($data->code);
        }

        return $this->gateway->sendVerificationMessage($data);
    }
}
