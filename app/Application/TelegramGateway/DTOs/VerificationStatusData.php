<?php

namespace App\Application\TelegramGateway\DTOs;

use App\Domain\TelegramGateway\Enums\VerificationStatus;
use Spatie\LaravelData\Data;

class VerificationStatusData extends Data
{
    public function __construct(
        public VerificationStatus $status,
        public int $updated_at,
        public ?string $code_entered = null,
    ) {}
}
