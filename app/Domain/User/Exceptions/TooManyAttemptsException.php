<?php

namespace App\Domain\User\Exceptions;

use Exception;

class TooManyAttemptsException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('auth.otp.too_many_attempts'));
    }
}
