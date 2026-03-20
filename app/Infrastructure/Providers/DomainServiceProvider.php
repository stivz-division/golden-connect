<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Services\TelegramGateway\TelegramGatewayInterface;
use App\Infrastructure\Services\TelegramGateway\TelegramGatewayService;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TelegramGatewayInterface::class, TelegramGatewayService::class);
    }

    public function boot(): void
    {
        //
    }
}
