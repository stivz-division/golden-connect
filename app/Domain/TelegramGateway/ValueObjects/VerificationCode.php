<?php

namespace App\Domain\TelegramGateway\ValueObjects;

use InvalidArgumentException;

final readonly class VerificationCode
{
    public string $value;

    public function __construct(string $value)
    {
        if (! preg_match('/^\d{4,8}$/', $value)) {
            throw new InvalidArgumentException("Verification code must be 4-8 digits: {$value}");
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function length(): int
    {
        return strlen($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
