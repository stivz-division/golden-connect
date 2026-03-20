<?php

namespace App\Application\User\Actions;

use App\Application\User\DTOs\RegisterWithCodeData;
use App\Application\User\DTOs\VerifyCodeData;
use App\Domain\User\Enums\ContactType;
use App\Domain\User\Exceptions\MentorNotFoundException;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterWithCodeAction
{
    public function __construct(
        private readonly VerifyCodeAction $verifyCode,
    ) {}

    public function execute(RegisterWithCodeData $data): User
    {
        $this->verifyCode->execute(
            new VerifyCodeData(
                identifier: $data->identifier,
                type: $data->type,
                code: $data->code,
            )
        );

        $mentor = $this->resolveMentor($data->ref);

        $user = DB::transaction(function () use ($data, $mentor) {
            $attributes = [
                'language' => session('locale', config('locales.default', 'ru')),
            ];

            match ($data->type) {
                ContactType::Phone => $attributes['phone'] = $data->identifier,
                ContactType::Email => $attributes['email'] = $data->identifier,
            };

            $user = new User($attributes);

            if ($mentor) {
                $user->appendToNode($mentor)->save();
            } else {
                $user->saveAsRoot();
            }

            Log::info('User registered via OTP', [
                'user_id' => $user->id,
                'type' => $data->type->value,
            ]);

            return $user;
        });

        Auth::login($user, remember: true);

        return $user;
    }

    private function resolveMentor(?string $ref): ?User
    {
        if ($ref === null || $ref === '') {
            return User::query()->orderBy('id')->first();
        }

        $mentor = User::where('uuid', $ref)->first();

        if (! $mentor) {
            throw new MentorNotFoundException($ref);
        }

        return $mentor;
    }
}
