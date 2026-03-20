<?php

namespace App\Http\Controllers\Webhook;

use App\Application\TelegramGateway\DTOs\DeliveryStatusData;
use App\Application\TelegramGateway\DTOs\RequestStatusData;
use App\Application\TelegramGateway\DTOs\VerificationStatusData;
use App\Domain\TelegramGateway\Enums\DeliveryStatus;
use App\Domain\TelegramGateway\Enums\VerificationStatus;
use App\Domain\TelegramGateway\Events\DeliveryStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\TelegramGatewayWebhookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TelegramGatewayWebhookController extends Controller
{
    public function __invoke(TelegramGatewayWebhookRequest $request): JsonResponse
    {
        Log::debug('TelegramGateway webhook received', [
            'request_id' => $request->input('request_id'),
        ]);

        $validated = $request->validated();

        $deliveryStatus = null;
        if (isset($validated['delivery_status'])) {
            $deliveryStatus = new DeliveryStatusData(
                status: DeliveryStatus::from($validated['delivery_status']['status']),
                updated_at: $validated['delivery_status']['updated_at'],
            );
        }

        $verificationStatus = null;
        if (isset($validated['verification_status'])) {
            $verificationStatus = new VerificationStatusData(
                status: VerificationStatus::from($validated['verification_status']['status']),
                updated_at: $validated['verification_status']['updated_at'],
                code_entered: $validated['verification_status']['code_entered'] ?? null,
            );
        }

        $requestStatus = new RequestStatusData(
            request_id: $validated['request_id'],
            phone_number: $validated['phone_number'],
            request_cost: (float) $validated['request_cost'],
            is_refunded: $validated['is_refunded'] ?? null,
            remaining_balance: isset($validated['remaining_balance']) ? (float) $validated['remaining_balance'] : null,
            delivery_status: $deliveryStatus,
            verification_status: $verificationStatus,
            payload: $validated['payload'] ?? null,
        );

        event(new DeliveryStatusUpdated($requestStatus));

        Log::debug('TelegramGateway webhook processed', [
            'request_id' => $validated['request_id'],
        ]);

        return response()->json(['ok' => true]);
    }
}
