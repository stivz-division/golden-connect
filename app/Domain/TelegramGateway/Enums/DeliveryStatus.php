<?php

namespace App\Domain\TelegramGateway\Enums;

enum DeliveryStatus: string
{
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
