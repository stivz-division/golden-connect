# Plan: Telegram Gateway API — полная реализация всех методов

- **Branch:** `feature/telegram-gateway-api`
- **Created:** 2026-03-21
- **Type:** Feature

## Settings

- **Testing:** Yes (Pest)
- **Logging:** No
- **Docs:** Yes (mandatory checkpoint)

## Description

Реализовать все методы Telegram Gateway API (`checkSendAbility`, `sendVerificationMessage`, `checkVerificationStatus`, `revokeVerificationMessage`) с обработкой webhook callback и полным DDD-подходом. Включает Domain-слой (Enums, ValueObjects, Exceptions), Application-слой (DTOs, Actions), Infrastructure-слой (HTTP-клиент, webhook controller).

## API Reference

**Base URL:** `https://gatewayapi.telegram.org/{METHOD_NAME}`
**Auth:** `Authorization: Bearer <token>`

### Методы:
1. `checkSendAbility(phone_number)` → RequestStatus
2. `sendVerificationMessage(phone_number, request_id?, sender_username?, code?, code_length?, callback_url?, payload?, ttl?)` → RequestStatus
3. `checkVerificationStatus(request_id, code?)` → RequestStatus
4. `revokeVerificationMessage(request_id)` → True

### Response Objects:
- **RequestStatus** — request_id, phone_number, request_cost, is_refunded?, remaining_balance?, delivery_status?, verification_status?, payload?
- **DeliveryStatus** — status (sent|delivered|read|expired|revoked), updated_at
- **VerificationStatus** — status (code_valid|code_invalid|code_max_attempts_exceeded|expired), updated_at, code_entered?

### Webhook:
- POST на callback_url с заголовками X-Request-Timestamp, X-Request-Signature
- Подпись: HMAC-SHA256(timestamp + '\n' + body, SHA256(api_token))

## Tasks

### Phase 1: Конфигурация

- [x] **Task 1:** Конфигурация и установка пакета
  - Добавить конфиг в `config/services.php` (telegram_gateway: token, base_url)
  - Добавить `TELEGRAM_GATEWAY_TOKEN` в `.env.example`
  - **Файлы:** `config/services.php`, `.env.example`

### Phase 2: Domain-слой

- [x] **Task 2:** Enums, ValueObjects, Exceptions `[blocked by: Task 1]`
  - `app/Domain/TelegramGateway/Enums/DeliveryStatus.php` — sent, delivered, read, expired, revoked
  - `app/Domain/TelegramGateway/Enums/VerificationStatus.php` — code_valid, code_invalid, code_max_attempts_exceeded, expired
  - `app/Domain/TelegramGateway/ValueObjects/PhoneNumber.php` — валидация E.164
  - `app/Domain/TelegramGateway/ValueObjects/VerificationCode.php` — 4-8 цифр
  - `app/Domain/TelegramGateway/Exceptions/TelegramGatewayException.php`
  - `app/Domain/TelegramGateway/Exceptions/InvalidTokenException.php`
  - `app/Domain/TelegramGateway/Exceptions/SendAbilityException.php`
  - `app/Domain/TelegramGateway/Exceptions/VerificationException.php`

- [x] **Task 3:** DTOs для запросов и ответов `[blocked by: Task 2]`
  - `app/Application/TelegramGateway/DTOs/SendVerificationData.php`
  - `app/Application/TelegramGateway/DTOs/CheckSendAbilityData.php`
  - `app/Application/TelegramGateway/DTOs/CheckVerificationStatusData.php`
  - `app/Application/TelegramGateway/DTOs/RevokeVerificationData.php`
  - `app/Application/TelegramGateway/DTOs/RequestStatusData.php`
  - `app/Application/TelegramGateway/DTOs/DeliveryStatusData.php`
  - `app/Application/TelegramGateway/DTOs/VerificationStatusData.php`

### Phase 3: Infrastructure

- [x] **Task 4:** TelegramGatewayService — HTTP-клиент `[blocked by: Task 2, 3]`
  - `app/Infrastructure/Services/TelegramGateway/TelegramGatewayInterface.php`
  - `app/Infrastructure/Services/TelegramGateway/TelegramGatewayService.php`
  - Привязка интерфейса в `app/Infrastructure/Providers/DomainServiceProvider.php`
  - 4 метода API, парсинг ok/error, маппинг в DTOs, кастомные Exception

- [x] **Task 5:** Webhook обработка callback_url `[blocked by: Task 2, 4]`
  - `app/Http/Controllers/Webhook/TelegramGatewayWebhookController.php`
  - `app/Http/Requests/Webhook/TelegramGatewayWebhookRequest.php`
  - `app/Domain/TelegramGateway/Events/DeliveryStatusUpdated.php`
  - Верификация HMAC-SHA256 подписи
  - Маршрут в `routes/api.php`

### Phase 4: Application

- [x] **Task 6:** Actions для бизнес-операций `[blocked by: Task 3, 4]`
  - `app/Application/TelegramGateway/Actions/CheckSendAbilityAction.php`
  - `app/Application/TelegramGateway/Actions/SendVerificationAction.php`
  - `app/Application/TelegramGateway/Actions/CheckVerificationStatusAction.php`
  - `app/Application/TelegramGateway/Actions/RevokeVerificationAction.php`

### Phase 5: Тестирование

- [x] **Task 7:** Unit и Feature тесты (Pest) `[blocked by: Task 2-6]`
  - `tests/Unit/Domain/TelegramGateway/` — ValueObjects, Enums
  - `tests/Unit/Infrastructure/TelegramGateway/` — Service с Http::fake()
  - `tests/Feature/Webhook/` — WebhookController
  - `tests/Unit/Application/TelegramGateway/` — Actions

## Commit Plan

### Commit 1 (после Task 1-3):
```
feat(telegram-gateway): добавить Domain-слой и DTOs для Telegram Gateway API
```

### Commit 2 (после Task 4-5):
```
feat(telegram-gateway): реализовать HTTP-клиент и webhook обработку
```

### Commit 3 (после Task 6):
```
feat(telegram-gateway): добавить Application Actions
```

### Commit 4 (после Task 7):
```
test(telegram-gateway): добавить Unit и Feature тесты
```

## Architecture Notes

- Новый Bounded Context: `TelegramGateway`
- Domain → чистая бизнес-логика (Enums, ValueObjects, Exceptions, Events)
- Application → use cases (Actions + DTOs через spatie/laravel-data)
- Infrastructure → HTTP-клиент (Laravel Http facade), webhook controller
- Интерфейс `TelegramGatewayInterface` для тестируемости и подменяемости
- Зависимости: Infrastructure → Application → Domain (строго внутрь)
