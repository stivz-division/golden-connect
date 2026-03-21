<?php

namespace App\Http\Controllers\Auth;

use App\Application\User\Actions\LoginWithCodeAction;
use App\Application\User\Actions\SendCodeAction;
use App\Application\User\DTOs\LoginWithCodeData;
use App\Application\User\DTOs\SendCodeData;
use App\Domain\TelegramGateway\Exceptions\PhoneNumberNotAvailableException;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Exceptions\TooManyAttemptsException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Exceptions\VerificationCodeExpiredException;
use App\Domain\User\Exceptions\VerificationCodeInvalidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SendCodeRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class LoginController extends Controller
{
    public function __construct(
        private readonly SendCodeAction $sendCode,
        private readonly LoginWithCodeAction $loginWithCode,
    ) {}

    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function sendCode(SendCodeRequest $request): RedirectResponse
    {
        try {
            $this->sendCode->execute(
                new SendCodeData(
                    identifier: $request->validated('identifier'),
                    type: ContactType::from($request->validated('type')),
                    requiresExistingUser: true,
                )
            );

            return back()->with('success', __('auth.otp.code_sent'));
        } catch (PhoneNumberNotAvailableException) {
            return back()->withErrors(['identifier' => __('auth.otp.phone_not_available')]);
        } catch (InvalidArgumentException) {
            return back()->withErrors(['identifier' => __('validation.phone_invalid_format')]);
        }
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $this->loginWithCode->execute(
                new LoginWithCodeData(
                    identifier: $request->validated('identifier'),
                    type: ContactType::from($request->validated('type')),
                    code: $request->validated('code'),
                )
            );

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        } catch (VerificationCodeInvalidException) {
            return back()->withErrors(['code' => __('auth.otp.code_invalid')]);
        } catch (PhoneNumberNotAvailableException) {
            return back()->withErrors(['identifier' => __('auth.otp.phone_not_available')]);
        } catch (VerificationCodeExpiredException) {
            return back()->withErrors(['code' => __('auth.otp.code_expired')]);
        } catch (TooManyAttemptsException) {
            return back()->withErrors(['code' => __('auth.otp.too_many_attempts')]);
        } catch (UserNotFoundException) {
            return back()->withErrors(['identifier' => __('auth.otp.user_not_found')]);
        }
    }
}
