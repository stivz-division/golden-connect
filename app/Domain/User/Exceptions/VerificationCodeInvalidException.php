<?php

namespace App\Domain\User\Exceptions;

use Exception;

class VerificationCodeInvalidException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('auth.otp.code_invalid'));
    }
}
