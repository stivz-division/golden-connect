<?php

use App\Domain\TelegramGateway\ValueObjects\PhoneNumber;

it('accepts valid E.164 phone number', function () {
    $phone = new PhoneNumber('+79991234567');

    expect($phone->toString())->toBe('+79991234567');
    expect((string) $phone)->toBe('+79991234567');
});

it('accepts valid international phone number', function () {
    $phone = new PhoneNumber('+14155551234');

    expect($phone->value)->toBe('+14155551234');
});

it('rejects phone number without plus', function () {
    new PhoneNumber('79991234567');
})->throws(InvalidArgumentException::class);

it('rejects phone number with letters', function () {
    new PhoneNumber('+7999abc4567');
})->throws(InvalidArgumentException::class);

it('rejects empty string', function () {
    new PhoneNumber('');
})->throws(InvalidArgumentException::class);

it('rejects phone number starting with +0', function () {
    new PhoneNumber('+09991234567');
})->throws(InvalidArgumentException::class);
