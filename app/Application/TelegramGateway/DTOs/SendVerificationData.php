<?php

namespace App\Application\TelegramGateway\DTOs;

use Spatie\LaravelData\Data;

class SendVerificationData extends Data
{
    public function __construct(
        public string $phone_number,
        public ?string $request_id = null,
        public ?string $sender_username = null,
        public ?string $code = null,
        public ?int $code_length = null,
        public ?string $callback_url = null,
        public ?string $payload = null,
        public ?int $ttl = null,
    ) {}
}
