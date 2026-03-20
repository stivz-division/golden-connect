<?php

namespace App\Domain\TelegramGateway\Exceptions;

class SendAbilityException extends TelegramGatewayException
{
    public function __construct(string $message = 'Cannot send verification message to this phone number')
    {
        parent::__construct($message, 400, 'SEND_ABILITY_ERROR');
    }
}
