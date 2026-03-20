<?php

namespace App\Domain\TelegramGateway\Exceptions;

use Exception;

class TelegramGatewayException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?string $errorCode = null,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
