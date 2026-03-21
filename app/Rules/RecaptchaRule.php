<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaRule implements ValidationRule
{
    public function __construct(
        private readonly string $expectedAction,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            Log::error('[RecaptchaRule] Secret key is not configured');
            $fail(__('validation.recaptcha_unavailable'));

            return;
        }

        try {
            $response = Http::timeout(5)
                ->asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                ]);
        } catch (\Throwable $e) {
            Log::error('[RecaptchaRule] Google API unavailable', [
                'error' => $e->getMessage(),
            ]);
            $fail(__('validation.recaptcha_unavailable'));

            return;
        }

        if (! $response->successful()) {
            Log::error('[RecaptchaRule] Google API returned non-200 status', [
                'status' => $response->status(),
            ]);
            $fail(__('validation.recaptcha_unavailable'));

            return;
        }

        $body = $response->json();

        if (! ($body['success'] ?? false)) {
            Log::warning('[RecaptchaRule] Verification failed', [
                'error-codes' => $body['error-codes'] ?? [],
            ]);
            $fail(__('validation.recaptcha_failed'));

            return;
        }

        $threshold = config('services.recaptcha.threshold', 0.5);
        $score = $body['score'] ?? 0;

        if ($score < $threshold) {
            Log::warning('[RecaptchaRule] Score below threshold', [
                'score' => $score,
                'threshold' => $threshold,
                'action' => $body['action'] ?? 'unknown',
            ]);
            $fail(__('validation.recaptcha_failed'));

            return;
        }

        if (($body['action'] ?? '') !== $this->expectedAction) {
            Log::warning('[RecaptchaRule] Action mismatch', [
                'expected' => $this->expectedAction,
                'actual' => $body['action'] ?? 'unknown',
            ]);
            $fail(__('validation.recaptcha_failed'));
        }
    }
}
