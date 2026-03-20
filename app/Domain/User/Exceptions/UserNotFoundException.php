<?php

namespace App\Domain\User\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('auth.otp.user_not_found'));
    }
}
