<?php

namespace App\Infrastructure\Services\TelegramGateway;

use App\Application\TelegramGateway\DTOs\CheckSendAbilityData;
use App\Application\TelegramGateway\DTOs\CheckVerificationStatusData;
use App\Application\TelegramGateway\DTOs\DeliveryStatusData;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Application\TelegramGateway\DTOs\RevokeVerificationData;
use App\Application\TelegramGateway\DTOs\SendVerificationData;
use App\Application\TelegramGateway\DTOs\VerificationStatusData;
use App\Domain\TelegramGateway\Enums\DeliveryStatus;
use App\Domain\TelegramGateway\Enums\VerificationStatus;
use App\Domain\TelegramGateway\Exceptions\InvalidTokenException;
use App\Domain\TelegramGateway\Exceptions\SendAbilityException;
use App\Domain\TelegramGateway\Exceptions\TelegramGatewayException;
use App\Domain\TelegramGateway\Exceptions\VerificationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramGatewayService implements TelegramGatewayInterface
{
    private string $token;

    private string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram_gateway.token');
        $this->baseUrl = config('services.telegram_gateway.base_url');
    }

    public function checkSendAbility(CheckSendAbilityData $data): RequestStatusData
    {
        Log::debug('TelegramGateway: checkSendAbility', ['phone_number' => $data->phone_number]);

        $response = $this->request()->post("{$this->baseUrl}/checkSendAbility", [
            'phone_number' => $data->phone_number,
        ]);

        $result = $this->parseResponse($response);

        Log::debug('TelegramGateway: checkSendAbility response', ['request_id' => $result['request_id']]);

        return $this->mapToRequestStatus($result);
    }

    public function sendVerificationMessage(SendVerificationData $data): RequestStatusData
    {
        Log::debug('TelegramGateway: sendVerificationMessage', ['phone_number' => $data->phone_number]);

        $payload = array_filter([
            'phone_number' => $data->phone_number,
            'request_id' => $data->request_id,
            'sender_username' => $data->sender_username,
            'code' => $data->code,
            'code_length' => $data->code_length,
            'callback_url' => $data->callback_url,
            'payload' => $data->payload,
            'ttl' => $data->ttl,
        ], fn ($value) => $value !== null);

        $response = $this->request()->post("{$this->baseUrl}/sendVerificationMessage", $payload);

        $result = $this->parseResponse($response);

        Log::debug('TelegramGateway: sendVerificationMessage response', ['request_id' => $result['request_id']]);

        return $this->mapToRequestStatus($result);
    }

    public function checkVerificationStatus(CheckVerificationStatusData $data): RequestStatusData
    {
        Log::debug('TelegramGateway: checkVerificationStatus', ['request_id' => $data->request_id]);

        $payload = array_filter([
            'request_id' => $data->request_id,
            'code' => $data->code,
        ], fn ($value) => $value !== null);

        $response = $this->request()->post("{$this->baseUrl}/checkVerificationStatus", $payload);

        $result = $this->parseResponse($response);

        Log::debug('TelegramGateway: checkVerificationStatus response', [
            'request_id' => $result['request_id'],
            'verification_status' => $result['verification_status'] ?? null,
        ]);

        return $this->mapToRequestStatus($result);
    }

    public function revokeVerificationMessage(RevokeVerificationData $data): bool
    {
        Log::debug('TelegramGateway: revokeVerificationMessage', ['request_id' => $data->request_id]);

        $response = $this->request()->post("{$this->baseUrl}/revokeVerificationMessage", [
            'request_id' => $data->request_id,
        ]);

        $body = $response->json();

        if (! ($body['ok'] ?? false)) {
            $this->handleError($body, $response->status());
        }

        Log::debug('TelegramGateway: revokeVerificationMessage success', ['request_id' => $data->request_id]);

        return true;
    }

    private function request(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->timeout(30);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws TelegramGatewayException
     */
    private function parseResponse(Response $response): array
    {
        $body = $response->json();

        if (! ($body['ok'] ?? false)) {
            $this->handleError($body, $response->status());
        }

        return $body['result'];
    }

    /**
     * @param  array<string, mixed>  $body
     *
     * @throws TelegramGatewayException
     */
    private function handleError(array $body, int $httpStatus): never
    {
        $errorMessage = $body['error'] ?? 'Unknown Telegram Gateway error';

        Log::error('TelegramGateway API error', [
            'error' => $errorMessage,
            'http_status' => $httpStatus,
        ]);

        if ($httpStatus === 401) {
            throw new InvalidTokenException;
        }

        if (str_contains($errorMessage, 'SEND_ABILITY') || str_contains($errorMessage, 'send ability')) {
            throw new SendAbilityException($errorMessage);
        }

        if (str_contains($errorMessage, 'VERIFICATION') || str_contains($errorMessage, 'verification')) {
            throw new VerificationException($errorMessage);
        }

        throw new TelegramGatewayException($errorMessage, $httpStatus);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function mapToRequestStatus(array $result): RequestStatusData
    {
        $deliveryStatus = null;
        if (isset($result['delivery_status'])) {
            $deliveryStatus = new DeliveryStatusData(
                status: DeliveryStatus::from($result['delivery_status']['status']),
                updated_at: $result['delivery_status']['updated_at'],
            );
        }

        $verificationStatus = null;
        if (isset($result['verification_status'])) {
            $verificationStatus = new VerificationStatusData(
                status: VerificationStatus::from($result['verification_status']['status']),
                updated_at: $result['verification_status']['updated_at'],
                code_entered: $result['verification_status']['code_entered'] ?? null,
            );
        }

        return new RequestStatusData(
            request_id: $result['request_id'],
            phone_number: $result['phone_number'],
            request_cost: (float) $result['request_cost'],
            is_refunded: $result['is_refunded'] ?? null,
            remaining_balance: isset($result['remaining_balance']) ? (float) $result['remaining_balance'] : null,
            delivery_status: $deliveryStatus,
            verification_status: $verificationStatus,
            payload: $result['payload'] ?? null,
        );
    }
}
