<?php

namespace App\Application\User\Actions;

use App\Application\User\DTOs\TelegramAuthData;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthViaTelegramAction
{
    public function execute(TelegramAuthData $data): ?User
    {
        $user = User::where('telegram_chat_id', $data->telegramId)->first();

        if ($user) {
            Auth::login($user, remember: true);

            Log::info('User authenticated via Telegram Mini App', [
                'user_id' => $user->id,
                'telegram_chat_id' => $data->telegramId,
            ]);

            return $user;
        }

        session()->put('telegram_auth', [
            'telegram_id' => $data->telegramId,
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'username' => $data->username,
            'language_code' => $data->languageCode,
        ]);

        Log::info('Telegram user not found, redirecting to registration', [
            'telegram_chat_id' => $data->telegramId,
        ]);

        return null;
    }
}
