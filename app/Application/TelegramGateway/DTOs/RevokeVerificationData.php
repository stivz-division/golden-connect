<?php

namespace App\Application\TelegramGateway\DTOs;

use Spatie\LaravelData\Data;

class RevokeVerificationData extends Data
{
    public function __construct(
        public string $request_id,
    ) {}
}
