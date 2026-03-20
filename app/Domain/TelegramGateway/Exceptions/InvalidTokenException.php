<?php

namespace App\Domain\TelegramGateway\Exceptions;

class InvalidTokenException extends TelegramGatewayException
{
    public function __construct()
    {
        parent::__construct('Invalid Telegram Gateway API token', 401, 'INVALID_TOKEN');
    }
}
