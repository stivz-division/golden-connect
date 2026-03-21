<?php

namespace App\Application\User\DTOs;

use Spatie\LaravelData\Data;

class TelegramAuthData extends Data
{
    public function __construct(
        public int $telegramId,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $username = null,
        public ?string $languageCode = null,
    ) {}
}
