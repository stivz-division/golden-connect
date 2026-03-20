<?php

namespace App\Domain\User\Enums;

enum ContactType: string
{
    case Phone = 'phone';
    case Email = 'email';
}
