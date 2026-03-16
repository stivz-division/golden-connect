# Architecture: Domain-Driven Design (DDD)

## Overview

Golden Connect использует DDD-архитектуру с ограниченными контекстами (Bounded Contexts), соответствующими ключевым бизнес-доменам: цикличная очередь Monar, реферальная программа, рекламная система, визитки и нетворкинг. Каждый контекст инкапсулирует свою доменную логику, модели и правила, взаимодействуя с другими через доменные события и чётко определённые интерфейсы.

DDD выбран потому что проект содержит сложную бизнес-логику (циклическая очередь с кругами, распределение средств по формулам, многоуровневая реферальная система, мировой пул) с множеством бизнес-правил и инвариантов, которые должны строго соблюдаться.

## Decision Rationale

- **Project type:** Финансовая платформа с очередями, балансами и распределением средств
- **Tech stack:** PHP 8.2+ / Laravel 12 / MySQL / Vue.js + Inertia.js / Redis
- **Key factor:** Высокая сложность домена — множество взаимосвязанных бизнес-правил, атомарные финансовые операции, несколько поддоменов с чёткими границами

## Bounded Contexts

| Контекст | Ответственность |
|----------|-----------------|
| **Monar** | Цикличная очередь, рабочие места, круги, технические лоты, реинвест |
| **Lot** | Активация лотов, абонентская плата, заморозка/разморозка, закрытие лотов |
| **Balance** | Три баланса пользователя, переводы, вывод средств, атомарные операции |
| **Referral** | Реферальное дерево (5 уровней), начисления, nested set |
| **WorldPool** | Накопление средств, ежемесячное распределение по 8 пулам |
| **Networking** | Выступления, коэффициенты, ежемесячное распределение |
| **Advertising** | Рекламные тексты, модерация, публикация в мессенджерах, дайджесты |
| **BusinessCard** | Электронные визитки, автоперевод, публикация в каналах |
| **Credit** | Кредитный лот $10, отдельная очередь, разблокировка |
| **User** | Регистрация, аутентификация, профиль, настройки |

## Folder Structure

```
app/
├── Domain/                          # Доменный слой (чистая бизнес-логика)
│   ├── Monar/
│   │   ├── Models/                  # Eloquent-модели домена
│   │   │   ├── WorkPlace.php
│   │   │   ├── Circle.php
│   │   │   └── MonarQueue.php
│   │   ├── ValueObjects/            # Неизменяемые объекты-значения
│   │   │   └── QueuePosition.php
│   │   ├── Events/                  # Доменные события
│   │   │   ├── CircleCompleted.php
│   │   │   └── WorkPlaceCreated.php
│   │   ├── Enums/
│   │   │   └── WorkPlaceStatus.php
│   │   └── Exceptions/
│   │       └── MonarQueueException.php
│   ├── Lot/
│   │   ├── Models/
│   │   │   ├── Lot.php
│   │   │   └── TechnicalLot.php
│   │   ├── ValueObjects/
│   │   │   ├── LotTier.php
│   │   │   └── SubscriptionFee.php
│   │   ├── Events/
│   │   │   ├── LotActivated.php
│   │   │   ├── LotClosed.php
│   │   │   └── LotFrozen.php
│   │   ├── Enums/
│   │   │   └── LotStatus.php
│   │   └── Exceptions/
│   ├── Balance/
│   │   ├── Models/
│   │   │   └── Transaction.php
│   │   ├── ValueObjects/
│   │   │   └── Money.php
│   │   ├── Events/
│   │   │   ├── BalanceCredited.php
│   │   │   └── BalanceDebited.php
│   │   ├── Enums/
│   │   │   ├── BalanceType.php
│   │   │   └── TransactionType.php
│   │   └── Exceptions/
│   │       └── InsufficientBalanceException.php
│   ├── Referral/
│   │   ├── Models/
│   │   │   └── Referral.php
│   │   ├── Events/
│   │   │   └── ReferralBonusPaid.php
│   │   └── Enums/
│   ├── WorldPool/
│   │   ├── Models/
│   │   │   └── WorldPoolEntry.php
│   │   ├── Events/
│   │   │   └── WorldPoolDistributed.php
│   │   └── Enums/
│   ├── Networking/
│   │   ├── Models/
│   │   │   └── Presentation.php
│   │   ├── Events/
│   │   └── Enums/
│   ├── Advertising/
│   │   ├── Models/
│   │   │   ├── AdText.php
│   │   │   ├── AdPublication.php
│   │   │   └── Digest.php
│   │   ├── Events/
│   │   │   └── AdTextApproved.php
│   │   ├── Enums/
│   │   │   ├── AdStatus.php
│   │   │   └── Messenger.php
│   │   └── Exceptions/
│   ├── BusinessCard/
│   │   ├── Models/
│   │   │   ├── BusinessCard.php
│   │   │   └── BusinessProject.php
│   │   ├── Events/
│   │   └── Enums/
│   ├── Credit/
│   │   ├── Models/
│   │   │   └── CreditLot.php
│   │   ├── Events/
│   │   └── Enums/
│   └── User/
│       ├── Models/
│       │   └── User.php
│       ├── Events/
│       │   └── UserRegistered.php
│       └── Enums/
│
├── Application/                     # Прикладной слой (use cases, actions)
│   ├── Monar/
│   │   ├── Actions/
│   │   │   ├── ProcessCircleAction.php
│   │   │   └── AddToQueueAction.php
│   │   └── DTOs/
│   │       └── CircleResultData.php
│   ├── Lot/
│   │   ├── Actions/
│   │   │   ├── ActivateLotAction.php
│   │   │   ├── CloseLotAction.php
│   │   │   ├── FreezeLotAction.php
│   │   │   └── ChargeSubscriptionAction.php
│   │   └── DTOs/
│   │       └── LotActivationData.php
│   ├── Balance/
│   │   ├── Actions/
│   │   │   ├── CreditBalanceAction.php
│   │   │   ├── DebitBalanceAction.php
│   │   │   ├── TransferBalanceAction.php
│   │   │   └── WithdrawAction.php
│   │   └── DTOs/
│   ├── Referral/
│   │   ├── Actions/
│   │   │   ├── DistributeReferralBonusAction.php
│   │   │   └── BuildReferralTreeAction.php
│   │   └── DTOs/
│   ├── WorldPool/
│   │   ├── Actions/
│   │   │   └── DistributeWorldPoolAction.php
│   │   └── DTOs/
│   ├── Networking/
│   │   ├── Actions/
│   │   │   ├── RecordPresentationAction.php
│   │   │   └── DistributeNetworkingFundAction.php
│   │   └── DTOs/
│   ├── Advertising/
│   │   ├── Actions/
│   │   │   ├── CreateAdTextAction.php
│   │   │   ├── ModerateAdTextAction.php
│   │   │   └── PublishAdAction.php
│   │   └── DTOs/
│   ├── BusinessCard/
│   │   ├── Actions/
│   │   │   ├── CreateBusinessCardAction.php
│   │   │   └── TranslateBusinessCardAction.php
│   │   └── DTOs/
│   ├── Credit/
│   │   ├── Actions/
│   │   │   ├── ActivateCreditLotAction.php
│   │   │   └── UnlockCreditAction.php
│   │   └── DTOs/
│   └── User/
│       ├── Actions/
│       │   └── RegisterUserAction.php
│       └── DTOs/
│
├── Infrastructure/                  # Инфраструктурный слой
│   ├── Jobs/                        # Laravel queue jobs
│   │   ├── Monar/
│   │   │   └── AddToMonarQueueJob.php
│   │   ├── Lot/
│   │   │   ├── CreateWorkPlacesJob.php
│   │   │   └── CreateTechLotsJob.php
│   │   ├── Referral/
│   │   │   └── ProcessReferralsJob.php
│   │   ├── Notification/
│   │   │   └── SendNotificationsJob.php
│   │   └── Billing/
│   │       └── ChargeSubscriptionJob.php
│   ├── Listeners/                   # Event listeners (cross-context)
│   │   ├── OnCircleCompleted.php
│   │   ├── OnLotActivated.php
│   │   └── OnLotClosed.php
│   ├── Services/                    # Внешние сервисы
│   │   ├── Web3/
│   │   │   └── Bep20Service.php
│   │   ├── Translation/
│   │   │   └── TranslationService.php
│   │   └── Messenger/
│   │       ├── TelegramPublisher.php
│   │       ├── WhatsAppPublisher.php
│   │       └── MessengerPublisherInterface.php
│   └── Providers/
│       └── DomainServiceProvider.php
│
├── Http/                            # Презентационный слой (Inertia)
│   ├── Controllers/
│   │   ├── Monar/
│   │   ├── Lot/
│   │   ├── Balance/
│   │   ├── Referral/
│   │   ├── Advertising/
│   │   ├── BusinessCard/
│   │   └── Dashboard/
│   ├── Requests/                    # Form Requests (валидация)
│   ├── Resources/                   # Inertia page data / API resources
│   └── Middleware/
│
├── Models/                          # (пустая — модели в Domain/)
└── Providers/
    ├── AppServiceProvider.php
    └── NovaServiceProvider.php

resources/
├── js/
│   ├── Pages/                       # Vue.js Inertia pages
│   │   ├── Dashboard/
│   │   ├── Lot/
│   │   ├── Balance/
│   │   ├── Advertising/
│   │   ├── BusinessCard/
│   │   ├── Referral/
│   │   ├── Statistics/
│   │   └── Auth/
│   ├── Components/                  # Переиспользуемые Vue-компоненты
│   ├── Composables/                 # Vue composables (useBalance, useLot, etc.)
│   ├── Layouts/                     # Layout компоненты
│   └── app.js                       # Entry point
├── css/
│   └── app.css                      # Tailwind CSS
└── lang/                            # Файлы локализации
    ├── ru/
    └── en/
```

## Dependency Rules

Зависимости направлены **внутрь** — внешние слои зависят от внутренних, но не наоборот.

- ✅ **Http → Application** — контроллеры вызывают Actions
- ✅ **Application → Domain** — Actions работают с моделями и событиями домена
- ✅ **Infrastructure → Application + Domain** — Jobs вызывают Actions, Listeners реагируют на Events
- ✅ **Domain → Domain (через Events)** — кросс-контекстная коммуникация через доменные события
- ❌ **Domain → Infrastructure** — домен не знает о Jobs, очередях, внешних сервисах
- ❌ **Domain → Http** — домен не знает о контроллерах и запросах
- ❌ **Application → Http** — Actions не зависят от HTTP-слоя
- ❌ **Прямой доступ между контекстами** — Lot не импортирует модели Monar напрямую

## Layer/Module Communication

### Внутри контекста
- Controller → Action → Model/Event — прямые вызовы

### Между контекстами
- **Доменные события (Laravel Events)** — основной механизм
- Пример: `LotActivated` → Listener создаёт Jobs для Monar, Referral, Notification
- Пример: `CircleCompleted` → Listener обновляет баланс через Balance Action

### Async обработка
- Тяжёлая логика выносится в **Laravel Jobs** (Horizon)
- Очередь `monar` — **строго 1 воркер** (sequential processing)
- Остальные очереди (`lots`, `referrals`, `notifications`, `billing`) — параллельные воркеры

## Key Principles

1. **Атомарные финансовые операции** — все операции с балансами через DB::transaction с оптимистичной блокировкой (`where('balance', '>=', $amount)->decrement()`). Никогда не читать баланс и потом записывать — только атомарный декремент.

2. **Один Action — одна бизнес-операция** — каждый Action в Application/ выполняет ровно одну задачу. Actions принимают DTO (`spatie/laravel-data`) и возвращают результат. Actions можно вызывать из Controllers, Jobs и других Actions.

3. **События для кросс-контекстной связи** — контексты не вызывают Actions друг друга напрямую. Используются доменные события + Listeners в Infrastructure/, которые диспатчат Jobs.

4. **Value Objects для бизнес-значений** — LotTier, Money, QueuePosition — immutable классы, валидирующие себя при создании. Enum для конечных наборов (LotStatus, BalanceType).

5. **DTO для передачи данных** — `spatie/laravel-data` для типизированной передачи между слоями. Request → DTO → Action → Response.

6. **Настраиваемые параметры через Nova Settings** — все изменяемые бизнес-параметры (проценты пулов, ставки) получаются через `nova_get_setting()` и регистрируются в `NovaServiceProvider`.

7. **Локализация всего пользовательского контента** — русский и английский для UI (`resources/lang/`), 46 языков для рекламного контента (через TranslationService).

## Code Examples

### Action с DTO (активация лота)

```php
// app/Application/Lot/DTOs/LotActivationData.php
use Spatie\LaravelData\Data;

class LotActivationData extends Data
{
    public function __construct(
        public int $userId,
        public int $amount,
    ) {}
}

// app/Application/Lot/Actions/ActivateLotAction.php
class ActivateLotAction
{
    public function execute(LotActivationData $data): Lot
    {
        $tier = LotTier::fromAmount($data->amount);

        // Атомарное списание с баланса
        $affected = DB::table('users')
            ->where('id', $data->userId)
            ->where('deposit_balance', '>=', $data->amount)
            ->decrement('deposit_balance', $data->amount);

        if ($affected === 0) {
            throw new InsufficientBalanceException();
        }

        $lot = Lot::create([
            'user_id' => $data->userId,
            'amount' => $data->amount,
            'status' => LotStatus::Active,
            'work_places_count' => $tier->workPlaces(),
            'tech_lots_count' => $tier->techLots(),
            'target_income' => $data->amount, // +100%
        ]);

        event(new LotActivated($lot));

        return $lot;
    }
}
```

### Value Object (LotTier)

```php
// app/Domain/Lot/ValueObjects/LotTier.php
class LotTier
{
    private const TIERS = [
        50 => ['places' => 2, 'income_pct' => 30, 'coefficient' => 1.0],
        100 => ['places' => 4, 'income_pct' => 35, 'coefficient' => 1.1],
        200 => ['places' => 7, 'income_pct' => 40, 'coefficient' => 1.2],
        300 => ['places' => 9, 'income_pct' => 50, 'coefficient' => 1.3],
        400 => ['places' => 12, 'income_pct' => 55, 'coefficient' => 1.4],
        500 => ['places' => 15, 'income_pct' => 60, 'coefficient' => 1.5],
        600 => ['places' => 18, 'income_pct' => 65, 'coefficient' => 1.6],
        700 => ['places' => 21, 'income_pct' => 70, 'coefficient' => 1.7],
        800 => ['places' => 24, 'income_pct' => 75, 'coefficient' => 1.8],
        900 => ['places' => 29, 'income_pct' => 80, 'coefficient' => 1.9],
        1000 => ['places' => 32, 'income_pct' => 90, 'coefficient' => 2.0],
    ];

    private function __construct(
        public readonly int $amount,
        public readonly array $config,
    ) {}

    public static function fromAmount(int $amount): self
    {
        if (!isset(self::TIERS[$amount])) {
            throw new \InvalidArgumentException("Invalid lot amount: {$amount}");
        }

        return new self($amount, self::TIERS[$amount]);
    }

    public function workPlaces(): int
    {
        return $this->config['places'];
    }

    public function techLots(): int
    {
        return ($this->amount - $this->workPlaces() * 10) / 10;
    }

    public function incomePercentage(): int
    {
        return $this->config['income_pct'];
    }

    public function incomePerCircle(): int
    {
        return ($this->config['income_pct'] / 100) * 10;
    }

    public function networkingCoefficient(): float
    {
        return $this->config['coefficient'];
    }
}
```

### Controller с Inertia (с обработкой ошибок для клиента)

```php
// app/Http/Controllers/Lot/LotController.php
use Illuminate\Http\Request;
use Inertia\Inertia;

class LotController extends Controller
{
    public function __construct(
        private ActivateLotAction $activateLot,
    ) {}

    public function store(ActivateLotRequest $request)
    {
        try {
            $this->activateLot->execute(
                LotActivationData::from($request->validated())
            );

            return redirect()
                ->route('lots.index')
                ->with('success', __('lot.activated'));
        } catch (InsufficientBalanceException) {
            return back()->withErrors([
                'amount' => __('lot.insufficient_balance'),
            ]);
        }
    }
}
```

### Listener для кросс-контекстной связи

```php
// app/Infrastructure/Listeners/OnLotActivated.php
class OnLotActivated
{
    public function handle(LotActivated $event): void
    {
        CreateWorkPlacesJob::dispatch($event->lot)
            ->onQueue('lots');

        CreateTechLotsJob::dispatch($event->lot)
            ->onQueue('lots');

        ProcessReferralsJob::dispatch($event->lot)
            ->onQueue('referrals');

        AddToMonarQueueJob::dispatch($event->lot)
            ->onQueue('monar');

        SendNotificationsJob::dispatch($event->lot->user, 'lot_activated')
            ->onQueue('notifications');
    }
}
```

### Horizon Queues Configuration

```php
// Очередь monar — СТРОГО 1 воркер (shared state)
'monar-worker' => [
    'connection' => 'redis',
    'queue' => ['monar'],
    'balance' => 'false',
    'processes' => 1,    // НИКОГДА не увеличивать
    'tries' => 3,
    'timeout' => 120,
],
```

## Anti-Patterns

- ❌ **Прямые вызовы между контекстами** — не импортировать Action из Lot внутри Monar. Использовать события.
- ❌ **Бизнес-логика в контроллерах** — контроллеры только принимают запрос, вызывают Action, возвращают ответ.
- ❌ **Чтение-запись баланса в два шага** — всегда атомарный `decrement()`/`increment()` с условием в WHERE.
- ❌ **Несколько воркеров на очередь `monar`** — это приведёт к race conditions в цикличной очереди.
- ❌ **Hardcode бизнес-параметров** — проценты, ставки, лимиты берутся из `nova_get_setting()` или конфигов.
- ❌ **Пропуск локализации** — весь пользовательский текст через `__()` / `trans()`, файлы в `resources/lang/ru/` и `resources/lang/en/`.
- ❌ **Модели в `app/Models/`** — модели располагаются в `app/Domain/{Context}/Models/`.
- ❌ **Забыть об Inertia-ошибках** — всегда возвращать ошибки через `withErrors()` или `back()->with()` для корректного отображения на клиенте.
