<?php

namespace App\Application\User\Actions;

use App\Application\User\DTOs\LoginWithCodeData;
use App\Application\User\DTOs\VerifyCodeData;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginWithCodeAction
{
    public function __construct(
        private readonly VerifyCodeAction $verifyCode,
    ) {}

    public function execute(LoginWithCodeData $data): User
    {
        $this->verifyCode->execute(
            new VerifyCodeData(
                identifier: $data->identifier,
                type: $data->type,
                code: $data->code,
            )
        );

        $user = match ($data->type) {
            ContactType::Phone => User::where('phone', $data->identifier)->first(),
            ContactType::Email => User::where('email', $data->identifier)->first(),
        };

        if (! $user) {
            throw new UserNotFoundException;
        }

        Auth::login($user, remember: true);

        Log::info('User logged in via OTP', [
            'user_id' => $user->id,
            'type' => $data->type->value,
        ]);

        return $user;
    }
}
