# Plan: Referral Stats — трекинг кликов и регистраций

**Branch:** `feature/referral-stats`
**Created:** 2026-03-22
**Type:** Feature

## Settings

- **Testing:** Yes
- **Logging:** Verbose (DEBUG)
- **Docs:** No (warn-only)

## Description

Добавить таблицу `referral_stats` для хранения статистики по реферальным ссылкам.
Два типа ссылок (web и telegram) — для каждой отдельные счётчики кликов и регистраций.
Суммарные показатели выводятся в компоненте `Invite/Index` через `stats.totalClicks` и `stats.registrations`.

## Tasks

### Phase 1: База данных и модель

**Task 1: ~~Создать миграцию для таблицы referral_stats~~ [x]**
- Файл: `database/migrations/XXXX_create_referral_stats_table.php`
- Колонки: `id`, `user_id` (FK, unique), `web_clicks`, `telegram_clicks`, `web_registrations`, `telegram_registrations`, `timestamps`
- Все счётчики `unsignedInteger`, default 0

**Task 2: ~~Создать модель ReferralStat в Domain/Referral~~ [x]**
- Файл: `app/Domain/Referral/Models/ReferralStat.php`
- Связь `belongsTo(User)`, обратная `hasOne` в User
- Аксессоры: `totalClicks`, `totalRegistrations`

### Phase 2: Трекинг событий

**Task 3: ~~Трекинг кликов по реферальной ссылке~~ [x]**
- Файл: `app/Application/Referral/Actions/TrackReferralClickAction.php`
- Вызов в `RegisterController::showRegistrationForm()` (web)
- Вызов в Telegram-контроллере (telegram)
- Атомарный `increment()` по соответствующему полю
- Логирование: `Log::debug('Referral click tracked', [...])`

**Task 4: ~~Трекинг регистраций по реферальной ссылке~~ [x]**
- Файл: `app/Application/Referral/Actions/TrackReferralRegistrationAction.php`
- Вызов в `RegisterWithCodeAction::execute()` после создания пользователя
- Определение источника (web/telegram) по сессии
- Атомарный `increment()`
- Логирование: `Log::info('Referral registration tracked', [...])`

### Phase 3: Отображение

**Task 5: ~~Обновить InviteController и компонент Invite/Index~~ [x]**
- Файл: `app/Http/Controllers/Invite/InviteController.php`
- Загрузить `ReferralStat` пользователя, передать реальные `totalClicks` и `registrations`
- Фронтенд уже использует `stats.totalClicks` и `stats.registrations` — проверить корректность

### Phase 4: Тесты

**Task 6: ~~Написать Pest-тесты~~ [x]**
- Файл: `tests/Feature/Referral/ReferralStatsTest.php`
- Тесты: модель, аксессоры, actions (click/registration), контроллер, feature-тест перехода

## Commit Plan

**Commit 1** (после Tasks 1-2):
```
feat(referral): добавить таблицу referral_stats и модель ReferralStat
```

**Commit 2** (после Tasks 3-5):
```
feat(referral): трекинг кликов и регистраций по реферальным ссылкам
```

**Commit 3** (после Task 6):
```
test(referral): добавить тесты для referral stats
```
