<?php

namespace App\Domain\TelegramGateway\Exceptions;

class VerificationException extends TelegramGatewayException
{
    public function __construct(string $message = 'Verification error')
    {
        parent::__construct($message, 400, 'VERIFICATION_ERROR');
    }
}
