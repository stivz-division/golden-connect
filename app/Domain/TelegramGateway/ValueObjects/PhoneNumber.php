<?php

namespace App\Domain\TelegramGateway\ValueObjects;

use InvalidArgumentException;

final readonly class PhoneNumber
{
    public string $value;

    public function __construct(string $value)
    {
        if (! preg_match('/^\+[1-9]\d{1,14}$/', $value)) {
            throw new InvalidArgumentException("Invalid E.164 phone number: {$value}");
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
