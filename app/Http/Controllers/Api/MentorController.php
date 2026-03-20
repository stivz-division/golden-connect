<?php

namespace App\Http\Controllers\Api;

use App\Domain\User\Exceptions\MentorNotFoundException;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MentorController extends Controller
{
    /**
     * @throws MentorNotFoundException
     */
    public function __invoke(string $uuid): JsonResponse
    {
        $mentor = User::where('uuid', $uuid)->first();

        if (! $mentor) {
            throw new MentorNotFoundException($uuid);
        }

        return response()->json([
            'uuid' => $mentor->uuid,
            'name' => $mentor->name,
            'surname' => $mentor->surname,
        ]);
    }
}
