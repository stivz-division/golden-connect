[← Deployment](deployment.md) · [Back to README](../README.md)

# Telegram Gateway API

Интеграция с [Telegram Gateway API](https://core.telegram.org/gateway) для отправки и верификации кодов подтверждения через Telegram.

## Конфигурация

Добавьте токен в `.env`:

```env
TELEGRAM_GATEWAY_TOKEN=your-api-token
```

Конфигурация в `config/services.php`:

| Параметр | Env-переменная | По умолчанию |
|----------|---------------|--------------|
| `token` | `TELEGRAM_GATEWAY_TOKEN` | — |
| `base_url` | `TELEGRAM_GATEWAY_BASE_URL` | `https://gatewayapi.telegram.org` |

## Архитектура

Реализация следует DDD-подходу проекта:

```
app/
├── Domain/TelegramGateway/
│   ├── Enums/
│   │   ├── DeliveryStatus.php        # sent, delivered, read, expired, revoked
│   │   └── VerificationStatus.php    # code_valid, code_invalid, code_max_attempts_exceeded, expired
│   ├── ValueObjects/
│   │   ├── PhoneNumber.php           # Валидация E.164
│   │   └── VerificationCode.php      # 4-8 цифр
│   ├── Events/
│   │   └── DeliveryStatusUpdated.php
│   └── Exceptions/
│       ├── TelegramGatewayException.php
│       ├── InvalidTokenException.php
│       ├── SendAbilityException.php
│       └── VerificationException.php
├── Application/TelegramGateway/
│   ├── Actions/
│   │   ├── CheckSendAbilityAction.php
│   │   ├── SendVerificationAction.php
│   │   ├── CheckVerificationStatusAction.php
│   │   └── RevokeVerificationAction.php
│   └── DTOs/
│       ├── CheckSendAbilityData.php
│       ├── SendVerificationData.php
│       ├── CheckVerificationStatusData.php
│       ├── RevokeVerificationData.php
│       ├── RequestStatusData.php
│       ├── DeliveryStatusData.php
│       └── VerificationStatusData.php
└── Infrastructure/Services/TelegramGateway/
    ├── TelegramGatewayInterface.php
    └── TelegramGatewayService.php
```

## Использование

### Проверка возможности отправки

```php
use App\Application\TelegramGateway\Actions\CheckSendAbilityAction;

$action = app(CheckSendAbilityAction::class);
$result = $action->execute('+79991234567');

// $result->request_id — ID запроса для дальнейших операций
// $result->request_cost — стоимость запроса
```

### Отправка кода верификации

```php
use App\Application\TelegramGateway\Actions\SendVerificationAction;
use App\Application\TelegramGateway\DTOs\SendVerificationData;

$action = app(SendVerificationAction::class);
$result = $action->execute(new SendVerificationData(
    phone_number: '+79991234567',
    code: '123456',
    code_length: 6,
    callback_url: route('webhook.telegram-gateway'),
    ttl: 300, // секунд
));
```

### Проверка статуса верификации

```php
use App\Application\TelegramGateway\Actions\CheckVerificationStatusAction;
use App\Application\TelegramGateway\DTOs\CheckVerificationStatusData;

$action = app(CheckVerificationStatusAction::class);
$result = $action->execute(new CheckVerificationStatusData(
    request_id: 'req_123',
    code: '123456', // опционально — проверка кода на стороне Telegram
));

// $result->verification_status->status — CodeValid, CodeInvalid, etc.
```

### Отзыв сообщения

```php
use App\Application\TelegramGateway\Actions\RevokeVerificationAction;

$action = app(RevokeVerificationAction::class);
$action->execute('req_123'); // true при успехе
```

## Webhook

Endpoint: `POST /api/webhook/telegram-gateway`

Telegram отправляет обновления статуса доставки на `callback_url`, указанный при отправке.

### Безопасность

Подпись проверяется автоматически через `TelegramGatewayWebhookRequest`:

- Заголовок `X-Request-Timestamp` — timestamp запроса
- Заголовок `X-Request-Signature` — HMAC-SHA256 подпись
- Ключ подписи: `SHA256(api_token)`
- Строка подписи: `timestamp + "\n" + body`

### Событие

При получении webhook диспатчится событие `DeliveryStatusUpdated`:

```php
use App\Domain\TelegramGateway\Events\DeliveryStatusUpdated;

// В Listener:
public function handle(DeliveryStatusUpdated $event): void
{
    $status = $event->requestStatus;
    // $status->request_id
    // $status->delivery_status->status — DeliveryStatus enum
    // $status->verification_status->status — VerificationStatus enum
}
```

## Обработка ошибок

| Exception | Когда возникает |
|-----------|----------------|
| `InvalidTokenException` | Невалидный API токен (HTTP 401) |
| `SendAbilityException` | Невозможно отправить на указанный номер |
| `VerificationException` | Ошибка верификации |
| `TelegramGatewayException` | Другие ошибки API |

Все исключения наследуются от `TelegramGatewayException`.

## Тестирование

```bash
./vendor/bin/pest --filter='TelegramGateway'
```

Тесты покрывают:
- ValueObjects (PhoneNumber, VerificationCode) — валидация
- Enums (DeliveryStatus, VerificationStatus) — маппинг значений
- TelegramGatewayService — все 4 метода API с `Http::fake()`
- WebhookController — проверка подписи, обработка payload
- Actions — валидация входных данных, вызов сервиса

## See Also

- [Configuration](configuration.md) — переменные окружения
- [Architecture](architecture.md) — DDD-структура проекта
