<?php

namespace App\Http\Controllers\Auth;

use App\Application\User\Actions\AuthViaTelegramAction;
use App\Application\User\DTOs\TelegramAuthData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TelegramAuthRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Micromagicman\TelegramWebApp\Service\TelegramWebAppService;

class TelegramAuthController extends Controller
{
    public function __construct(
        private readonly AuthViaTelegramAction $authViaTelegram,
    ) {}

    public function load(): Response
    {
        return Inertia::render('Auth/TelegramLoad');
    }

    public function auth(TelegramAuthRequest $request, TelegramWebAppService $telegramWebAppService): RedirectResponse
    {
        $telegramUser = $telegramWebAppService->getWebAppUser($request);

        if (! $telegramUser) {
            return redirect()->route('login')
                ->withErrors(['telegram' => __('telegram.invalid_data')]);
        }

        $user = $this->authViaTelegram->execute(
            new TelegramAuthData(
                telegramId: $telegramUser->getId(),
                firstName: rescue(fn () => $telegramUser->getFirstName()),
                lastName: rescue(fn () => $telegramUser->getLastName()),
                username: rescue(fn () => $telegramUser->getUsername()),
                languageCode: rescue(fn () => $telegramUser->getLanguageCode()),
            )
        );

        if ($user) {
            return redirect()->route('dashboard');
        }

        session()->put('telegram_linked', true);

        return redirect()->route('register');
    }
}
