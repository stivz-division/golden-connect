<?php

use App\Http\Controllers\Api\MentorController;
use App\Http\Controllers\Webhook\TelegramGatewayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/mentor/{uuid}', MentorController::class)->name('api.mentor.show');

Route::post('/webhook/telegram-gateway', TelegramGatewayWebhookController::class)
    ->name('webhook.telegram-gateway');
