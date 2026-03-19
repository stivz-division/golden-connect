<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MentorNotFoundException extends Exception
{
    public function __construct(
        private readonly string $login,
    ) {
        parent::__construct("Mentor not found: {$login}");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => "Mentor with login '{$this->login}' not found",
        ], 404);
    }

    public function report(): void
    {
        Log::warning('Mentor not found', ['login' => $this->login]);
    }
}
