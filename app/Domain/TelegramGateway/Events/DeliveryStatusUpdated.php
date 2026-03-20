<?php

namespace App\Domain\TelegramGateway\Events;

use App\Application\TelegramGateway\DTOs\RequestStatusData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly RequestStatusData $requestStatus,
    ) {}
}
