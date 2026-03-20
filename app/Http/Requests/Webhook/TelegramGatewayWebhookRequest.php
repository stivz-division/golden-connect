<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class TelegramGatewayWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->verifySignature();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'request_id' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
            'request_cost' => ['required', 'numeric'],
            'is_refunded' => ['sometimes', 'boolean'],
            'remaining_balance' => ['sometimes', 'numeric'],
            'delivery_status' => ['sometimes', 'array'],
            'delivery_status.status' => ['required_with:delivery_status', 'string'],
            'delivery_status.updated_at' => ['required_with:delivery_status', 'integer'],
            'verification_status' => ['sometimes', 'array'],
            'verification_status.status' => ['required_with:verification_status', 'string'],
            'verification_status.updated_at' => ['required_with:verification_status', 'integer'],
            'verification_status.code_entered' => ['sometimes', 'string'],
            'payload' => ['sometimes', 'string'],
        ];
    }

    private function verifySignature(): bool
    {
        $timestamp = $this->header('X-Request-Timestamp');
        $signature = $this->header('X-Request-Signature');

        if (! $timestamp || ! $signature) {
            Log::warning('TelegramGateway webhook: missing signature headers');

            return false;
        }

        $token = config('services.telegram_gateway.token');
        $secretKey = hash('sha256', $token, true);
        $dataCheckString = $timestamp."\n".$this->getContent();
        $expectedSignature = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('TelegramGateway webhook: invalid signature');

            return false;
        }

        return true;
    }
}
