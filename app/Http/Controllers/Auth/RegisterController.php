<?php

namespace App\Http\Controllers\Auth;

use App\Application\Referral\Actions\TrackReferralClickAction;
use App\Application\User\Actions\RegisterWithCodeAction;
use App\Application\User\Actions\SendCodeAction;
use App\Application\User\DTOs\RegisterWithCodeData;
use App\Application\User\DTOs\SendCodeData;
use App\Domain\Referral\Enums\ReferralSource;
use App\Domain\TelegramGateway\Exceptions\PhoneNumberNotAvailableException;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Exceptions\MentorNotFoundException;
use App\Domain\User\Exceptions\TooManyAttemptsException;
use App\Domain\User\Exceptions\VerificationCodeExpiredException;
use App\Domain\User\Exceptions\VerificationCodeInvalidException;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendCodeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class RegisterController extends Controller
{
    public function __construct(
        private readonly SendCodeAction $sendCode,
        private readonly RegisterWithCodeAction $registerWithCode,
        private readonly TrackReferralClickAction $trackReferralClick,
    ) {}

    public function showRegistrationForm(Request $request): Response
    {
        $ref = $request->query('ref');
        $mentorUuid = $ref ?: User::query()->orderBy('id')->value('uuid');

        if ($ref && ! session()->has("referral_click_tracked_{$ref}")) {
            $mentor = User::where('uuid', $ref)->first();

            if ($mentor) {
                $source = session('telegram_linked', false)
                    ? ReferralSource::Telegram
                    : ReferralSource::Web;

                $this->trackReferralClick->execute($mentor->id, $source);
                session()->put("referral_click_tracked_{$ref}", true);
            }
        }

        return Inertia::render('Auth/Register', [
            'ref' => $ref,
            'mentorUuid' => $mentorUuid,
            'telegramLinked' => session('telegram_linked', false),
        ]);
    }

    public function sendCode(SendCodeRequest $request): RedirectResponse
    {
        try {
            $this->sendCode->execute(
                new SendCodeData(
                    identifier: $request->validated('identifier'),
                    type: ContactType::from($request->validated('type')),
                )
            );

            return back()->with('success', __('auth.otp.code_sent'));
        } catch (PhoneNumberNotAvailableException) {
            return back()->withErrors(['identifier' => __('auth.otp.phone_not_available')]);
        } catch (InvalidArgumentException) {
            return back()->withErrors(['identifier' => __('validation.phone_invalid_format')]);
        }
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $this->registerWithCode->execute(
                new RegisterWithCodeData(
                    identifier: $request->validated('identifier'),
                    type: ContactType::from($request->validated('type')),
                    code: $request->validated('code'),
                    ref: $request->validated('ref'),
                )
            );

            return redirect()->route('dashboard');
        } catch (VerificationCodeInvalidException) {
            return back()->withErrors(['code' => __('auth.otp.code_invalid')]);
        } catch (PhoneNumberNotAvailableException) {
            return back()->withErrors(['identifier' => __('auth.otp.phone_not_available')]);
        } catch (VerificationCodeExpiredException) {
            return back()->withErrors(['code' => __('auth.otp.code_expired')]);
        } catch (TooManyAttemptsException) {
            return back()->withErrors(['code' => __('auth.otp.too_many_attempts')]);
        } catch (MentorNotFoundException) {
            return back()->withErrors(['ref' => __('auth.mentorNotFoundMessage')]);
        }
    }
}
