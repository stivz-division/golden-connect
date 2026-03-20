<?php

namespace App\Application\TelegramGateway\DTOs;

use App\Domain\TelegramGateway\Enums\DeliveryStatus;
use Spatie\LaravelData\Data;

class DeliveryStatusData extends Data
{
    public function __construct(
        public DeliveryStatus $status,
        public int $updated_at,
    ) {}
}
