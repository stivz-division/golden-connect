<?php

namespace App\Domain\TelegramGateway\Enums;

enum VerificationStatus: string
{
    case CodeValid = 'code_valid';
    case CodeInvalid = 'code_invalid';
    case CodeMaxAttemptsExceeded = 'code_max_attempts_exceeded';
    case Expired = 'expired';
}
