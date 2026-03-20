<?php

namespace App\Application\TelegramGateway\DTOs;

use Spatie\LaravelData\Data;

class CheckVerificationStatusData extends Data
{
    public function __construct(
        public string $request_id,
        public ?string $code = null,
    ) {}
}
