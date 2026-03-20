<?php

namespace App\Application\User\DTOs;

use App\Domain\User\Enums\ContactType;
use Spatie\LaravelData\Data;

class SendCodeData extends Data
{
    public function __construct(
        public string $identifier,
        public ContactType $type,
        public bool $requiresExistingUser = false,
    ) {}
}
