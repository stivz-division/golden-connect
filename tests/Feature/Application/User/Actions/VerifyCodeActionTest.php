<?php

use App\Application\User\Actions\VerifyCodeAction;
use App\Application\User\DTOs\VerifyCodeData;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Exceptions\TooManyAttemptsException;
use App\Domain\User\Exceptions\VerificationCodeExpiredException;
use App\Domain\User\Exceptions\VerificationCodeInvalidException;
use App\Domain\User\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('verifies valid code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $action = new VerifyCodeAction;

    $result = $action->execute(new VerifyCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
        code: '123456',
    ));

    expect($result->verified_at)->not->toBeNull();
});

it('throws exception for invalid code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $action = new VerifyCodeAction;

    $action->execute(new VerifyCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
        code: '000000',
    ));
})->throws(VerificationCodeInvalidException::class);

it('increments attempts on wrong code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $action = new VerifyCodeAction;

    try {
        $action->execute(new VerifyCodeData(
            identifier: '+79991234567',
            type: ContactType::Phone,
            code: '000000',
        ));
    } catch (VerificationCodeInvalidException) {
        // expected
    }

    $code = VerificationCode::where('identifier', '+79991234567')->first();
    expect($code->attempts)->toBe(1);
});

it('throws exception for expired code', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->subMinute(),
    ]);

    $action = new VerifyCodeAction;

    $action->execute(new VerifyCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
        code: '123456',
    ));
})->throws(VerificationCodeExpiredException::class);

it('throws exception after too many attempts', function () {
    VerificationCode::create([
        'identifier' => '+79991234567',
        'type' => 'phone',
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
        'attempts' => 5,
    ]);

    $action = new VerifyCodeAction;

    $action->execute(new VerifyCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
        code: '123456',
    ));
})->throws(TooManyAttemptsException::class);

it('throws exception when no code exists', function () {
    $action = new VerifyCodeAction;

    $action->execute(new VerifyCodeData(
        identifier: '+79991234567',
        type: ContactType::Phone,
        code: '123456',
    ));
})->throws(VerificationCodeInvalidException::class);
