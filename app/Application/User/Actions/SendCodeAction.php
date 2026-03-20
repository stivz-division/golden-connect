<?php

namespace App\Application\User\Actions;

use App\Application\TelegramGateway\Actions\SendVerificationAction;
use App\Application\TelegramGateway\DTOs\SendVerificationData;
use App\Application\User\DTOs\SendCodeData;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Models\User;
use App\Domain\User\Models\VerificationCode;
use App\Notifications\Auth\SendVerificationCodeNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SendCodeAction
{
    public function __construct(
        private readonly SendVerificationAction $sendVerification,
    ) {}

    public function execute(SendCodeData $data): void
    {
        $rateLimiterKey = 'send-code:'.$data->identifier;

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);

            throw ValidationException::withMessages([
                'identifier' => __('auth.otp.rate_limited', ['seconds' => $seconds]),
            ]);
        }

        if ($data->requiresExistingUser && ! $this->userExists($data)) {
            RateLimiter::hit($rateLimiterKey, 60);

            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'identifier' => $data->identifier,
            'type' => $data->type,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        match ($data->type) {
            ContactType::Phone => $this->sendViaPhone($data->identifier, $code),
            ContactType::Email => $this->sendViaEmail($data->identifier, $code),
        };

        RateLimiter::hit($rateLimiterKey, 60);
    }

    private function userExists(SendCodeData $data): bool
    {
        return match ($data->type) {
            ContactType::Phone => User::where('phone', $data->identifier)->exists(),
            ContactType::Email => User::where('email', $data->identifier)->exists(),
        };
    }

    private function sendViaPhone(string $phone, string $code): void
    {
        $this->sendVerification->execute(
            new SendVerificationData(
                phone_number: $phone,
                code: $code,
                ttl: 300,
            )
        );

        Log::info('Verification code sent via Telegram Gateway', ['phone' => $phone]);
    }

    private function sendViaEmail(string $email, string $code): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->notify(new SendVerificationCodeNotification($code));
        } else {
            Notification::route('mail', $email)
                ->notify(new SendVerificationCodeNotification($code));
        }

        Log::info('Verification code sent via email', ['email' => $email]);
    }
}
