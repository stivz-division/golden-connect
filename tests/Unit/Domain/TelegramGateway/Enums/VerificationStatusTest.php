<?php

use App\Domain\TelegramGateway\Enums\VerificationStatus;

it('has all expected cases', function () {
    expect(VerificationStatus::cases())->toHaveCount(4);
});

it('can be created from string value', function () {
    expect(VerificationStatus::from('code_valid'))->toBe(VerificationStatus::CodeValid);
    expect(VerificationStatus::from('code_invalid'))->toBe(VerificationStatus::CodeInvalid);
    expect(VerificationStatus::from('code_max_attempts_exceeded'))->toBe(VerificationStatus::CodeMaxAttemptsExceeded);
    expect(VerificationStatus::from('expired'))->toBe(VerificationStatus::Expired);
});

it('throws on invalid value', function () {
    VerificationStatus::from('invalid');
})->throws(ValueError::class);
