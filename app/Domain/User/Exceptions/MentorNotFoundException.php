<?php

namespace App\Domain\User\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MentorNotFoundException extends Exception
{
    public function __construct(
        private readonly string $identifier,
    ) {
        parent::__construct("Mentor not found: {$identifier}");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => "Mentor with identifier '{$this->identifier}' not found",
        ], 404);
    }

    public function report(): void
    {
        Log::warning('Mentor not found', ['identifier' => $this->identifier]);
    }
}
