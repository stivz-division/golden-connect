<?php

use App\Domain\TelegramGateway\Events\DeliveryStatusUpdated;
use Illuminate\Support\Facades\Event;

it('processes valid webhook with correct signature', function () {
    Event::fake([DeliveryStatusUpdated::class]);

    $token = 'test-token';
    config(['services.telegram_gateway.token' => $token]);

    $body = json_encode([
        'request_id' => 'req_123',
        'phone_number' => '+79991234567',
        'request_cost' => 0.5,
        'delivery_status' => [
            'status' => 'delivered',
            'updated_at' => 1700000000,
        ],
    ]);

    $timestamp = (string) time();
    $secretKey = hash('sha256', $token, true);
    $signature = hash_hmac('sha256', $timestamp."\n".$body, $secretKey);

    $response = $this->call('POST', '/api/webhook/telegram-gateway', [], [], [], [
        'HTTP_X-Request-Timestamp' => $timestamp,
        'HTTP_X-Request-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertOk();
    $response->assertJson(['ok' => true]);

    Event::assertDispatched(DeliveryStatusUpdated::class, function ($event) {
        return $event->requestStatus->request_id === 'req_123'
            && $event->requestStatus->phone_number === '+79991234567';
    });
});

it('rejects webhook with invalid signature', function () {
    config(['services.telegram_gateway.token' => 'test-token']);

    $body = json_encode([
        'request_id' => 'req_123',
        'phone_number' => '+79991234567',
        'request_cost' => 0.5,
    ]);

    $response = $this->call('POST', '/api/webhook/telegram-gateway', [], [], [], [
        'HTTP_X-Request-Timestamp' => (string) time(),
        'HTTP_X-Request-Signature' => 'invalid-signature',
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertForbidden();
});

it('rejects webhook without signature headers', function () {
    config(['services.telegram_gateway.token' => 'test-token']);

    $response = $this->postJson('/api/webhook/telegram-gateway', [
        'request_id' => 'req_123',
        'phone_number' => '+79991234567',
        'request_cost' => 0.5,
    ]);

    $response->assertForbidden();
});
