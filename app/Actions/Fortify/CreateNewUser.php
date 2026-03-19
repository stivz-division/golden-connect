<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'login' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $refLogin = $input['ref'] ?? null;
        $mentor = $this->resolveMentor($refLogin);

        return DB::transaction(function () use ($input, $mentor) {
            $user = new User([
                'login' => $input['login'],
                'name' => $input['name'],
                'surname' => $input['surname'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'language' => session('locale', config('locales.default', 'ru')),
            ]);

            if ($mentor) {
                $user->appendToNode($mentor)->save();
            } else {
                $user->saveAsRoot();
            }

            return $user;
        });
    }

    /**
     * @throws ValidationException
     */
    private function resolveMentor(?string $refLogin): ?User
    {
        if ($refLogin === null || $refLogin === '') {
            $firstUser = User::query()->orderBy('id')->first();

            return $firstUser;
        }

        $mentor = User::where('login', $refLogin)->first();

        if (! $mentor) {
            throw ValidationException::withMessages([
                'ref' => [__('auth.mentorNotFoundMessage')],
            ]);
        }

        Log::debug('Referral mentor resolved', [
            'mentor' => $mentor->login,
        ]);

        return $mentor;
    }
}
