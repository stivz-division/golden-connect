<?php

namespace App\Application\User\DTOs;

use App\Domain\User\Enums\ContactType;
use Spatie\LaravelData\Data;

class RegisterWithCodeData extends Data
{
    public function __construct(
        public string $identifier,
        public ContactType $type,
        public string $code,
        public ?string $ref = null,
    ) {}
}
