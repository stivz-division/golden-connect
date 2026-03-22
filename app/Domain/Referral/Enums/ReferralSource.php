<?php

namespace App\Domain\Referral\Enums;

enum ReferralSource: string
{
    case Web = 'web';
    case Telegram = 'telegram';
}
