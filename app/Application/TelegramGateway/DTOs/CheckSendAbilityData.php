<?php

namespace App\Application\TelegramGateway\DTOs;

use Spatie\LaravelData\Data;

class CheckSendAbilityData extends Data
{
    public function __construct(
        public string $phone_number,
    ) {}
}
