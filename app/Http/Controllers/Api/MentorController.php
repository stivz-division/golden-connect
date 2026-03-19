<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\MentorNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class MentorController extends Controller
{
    /**
     * @throws MentorNotFoundException
     */
    public function __invoke(string $login): JsonResponse
    {
        $mentor = User::where('login', $login)->first();

        if (! $mentor) {
            throw new MentorNotFoundException($login);
        }

        return response()->json([
            'name' => $mentor->name,
            'surname' => $mentor->surname,
            'login' => $mentor->login,
        ]);
    }
}
