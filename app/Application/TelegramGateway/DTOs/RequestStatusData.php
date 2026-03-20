<?php

namespace App\Application\TelegramGateway\DTOs;

use Spatie\LaravelData\Data;

class RequestStatusData extends Data
{
    public function __construct(
        public string $request_id,
        public string $phone_number,
        public float $request_cost,
        public ?bool $is_refunded = null,
        public ?float $remaining_balance = null,
        public ?DeliveryStatusData $delivery_status = null,
        public ?VerificationStatusData $verification_status = null,
        public ?string $payload = null,
    ) {}
}
