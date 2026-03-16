[← Getting Started](getting-started.md) · [Back to README](../README.md) · [Configuration →](configuration.md)

# Architecture

Golden Connect использует **DDD (Domain-Driven Design)** с 10 bounded contexts.

Подробные архитектурные решения — см. [.ai-factory/ARCHITECTURE.md](../.ai-factory/ARCHITECTURE.md).

## Bounded Contexts

| Контекст | Ответственность |
|----------|-----------------|
| **Monar** | Цикличная очередь, рабочие места, круги, реинвест |
| **Lot** | Активация лотов, абонентская плата, заморозка, закрытие |
| **Balance** | 3 баланса пользователя, переводы, вывод средств |
| **Referral** | Реферальное дерево (5 уровней), начисления |
| **WorldPool** | Накопление и ежемесячное распределение по 8 пулам |
| **Networking** | Выступления, коэффициенты, распределение фонда |
| **Advertising** | Рекламные тексты, модерация, публикация, дайджесты |
| **BusinessCard** | Визитки, автоперевод, публикация в каналах |
| **Credit** | Кредитный лот $10, отдельная очередь |
| **User** | Регистрация, аутентификация, профиль |

## Project Structure

```
app/
├── Domain/                 # Доменный слой (чистая бизнес-логика)
│   ├── {Context}/
│   │   ├── Models/         # Eloquent-модели
│   │   ├── ValueObjects/   # Immutable value objects
│   │   ├── Events/         # Доменные события
│   │   ├── Enums/          # Enum-классы
│   │   └── Exceptions/     # Доменные исключения
│
├── Application/            # Прикладной слой (use cases)
│   ├── {Context}/
│   │   ├── Actions/        # Бизнес-операции
│   │   └── DTOs/           # Data Transfer Objects (spatie/laravel-data)
│
├── Infrastructure/         # Инфраструктурный слой
│   ├── Jobs/               # Queue jobs (по контекстам)
│   ├── Listeners/          # Event listeners (кросс-контекстные)
│   ├── Services/           # Внешние сервисы (Web3, Translation, Messenger)
│   └── Providers/          # Service providers
│
├── Http/                   # Презентационный слой
│   ├── Controllers/        # Inertia controllers (по контекстам)
│   ├── Requests/           # Form Requests
│   ├── Resources/          # API Resources
│   └── Middleware/
│
resources/js/
├── Pages/                  # Vue.js Inertia pages
├── Components/             # Переиспользуемые компоненты
├── Composables/            # Vue composables
└── Layouts/                # Layout компоненты
```

## Dependency Rules

- **Http → Application** — контроллеры вызывают Actions
- **Application → Domain** — Actions работают с моделями и событиями
- **Infrastructure → Application + Domain** — Jobs вызывают Actions
- **Domain → Domain (через Events)** — кросс-контекстная коммуникация
- **Domain НЕ зависит от Infrastructure и Http**

## Key Patterns

| Паттерн | Использование |
|---------|---------------|
| **Actions** | Одна бизнес-операция = один Action класс |
| **DTO** | `spatie/laravel-data` для типизированной передачи данных |
| **Domain Events** | Связь между контекстами через Laravel Events |
| **Value Objects** | LotTier, Money, QueuePosition — immutable |
| **Atomic Operations** | `DB::table()->where('balance', '>=', $amount)->decrement()` |

## Queue Architecture

| Очередь | Воркеры | Назначение |
|---------|---------|------------|
| `monar` | **1** (строго) | Цикличная очередь — shared state |
| `lots` | 4 | Создание рабочих мест, тех. лотов |
| `referrals` | 2 | Реферальные начисления |
| `notifications` | 3 | Уведомления |
| `billing` | 2 | Абонентская плата, заморозки |
| `default` | 2 | Прочие задачи |

## See Also

- [Getting Started](getting-started.md) — установка и первый запуск
- [Configuration](configuration.md) — переменные окружения и настройки
