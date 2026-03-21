<?php

namespace App\Application\User\Actions;

use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Log;

class LinkTelegramAction
{
    public function execute(User $user): void
    {
        $telegramData = session()->pull('telegram_auth');
        session()->forget('telegram_linked');

        if (! $telegramData) {
            return;
        }

        $user->update([
            'telegram_chat_id' => $telegramData['telegram_id'],
        ]);

        Log::info('Telegram linked to user after registration', [
            'user_id' => $user->id,
            'telegram_chat_id' => $telegramData['telegram_id'],
        ]);
    }
}
