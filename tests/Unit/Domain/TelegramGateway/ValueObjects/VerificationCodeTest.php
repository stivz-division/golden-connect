<?php

use App\Domain\TelegramGateway\ValueObjects\VerificationCode;

it('accepts valid 4-digit code', function () {
    $code = new VerificationCode('1234');

    expect($code->toString())->toBe('1234');
    expect($code->length())->toBe(4);
});

it('accepts valid 8-digit code', function () {
    $code = new VerificationCode('12345678');

    expect($code->toString())->toBe('12345678');
    expect($code->length())->toBe(8);
});

it('accepts valid 6-digit code', function () {
    $code = new VerificationCode('123456');

    expect($code->length())->toBe(6);
});

it('rejects code shorter than 4 digits', function () {
    new VerificationCode('123');
})->throws(InvalidArgumentException::class);

it('rejects code longer than 8 digits', function () {
    new VerificationCode('123456789');
})->throws(InvalidArgumentException::class);

it('rejects code with letters', function () {
    new VerificationCode('12ab');
})->throws(InvalidArgumentException::class);
