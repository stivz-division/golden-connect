<?php

namespace App\Domain\TelegramGateway\Exceptions;

class PhoneNumberNotAvailableException extends TelegramGatewayException
{
    public function __construct(string $message = 'Phone number is not available on Telegram')
    {
        parent::__construct($message, 400, 'PHONE_NUMBER_NOT_AVAILABLE');
    }
}
