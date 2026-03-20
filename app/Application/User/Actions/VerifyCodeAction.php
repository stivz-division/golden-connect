<?php

namespace App\Application\User\Actions;

use App\Application\User\DTOs\VerifyCodeData;
use App\Domain\User\Exceptions\TooManyAttemptsException;
use App\Domain\User\Exceptions\VerificationCodeExpiredException;
use App\Domain\User\Exceptions\VerificationCodeInvalidException;
use App\Domain\User\Models\VerificationCode;

class VerifyCodeAction
{
    public function execute(VerifyCodeData $data): VerificationCode
    {
        $verificationCode = VerificationCode::forIdentifier($data->identifier)
            ->where('type', $data->type)
            ->latest()
            ->first();

        if (! $verificationCode) {
            throw new VerificationCodeInvalidException;
        }

        if ($verificationCode->isVerified()) {
            throw new VerificationCodeInvalidException;
        }

        if ($verificationCode->hasExceededAttempts()) {
            throw new TooManyAttemptsException;
        }

        if ($verificationCode->isExpired()) {
            throw new VerificationCodeExpiredException;
        }

        if ($verificationCode->code !== $data->code) {
            $verificationCode->increment('attempts');

            throw new VerificationCodeInvalidException;
        }

        $verificationCode->update(['verified_at' => now()]);

        return $verificationCode;
    }
}
