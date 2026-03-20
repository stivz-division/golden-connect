# Feature: OTP-авторизация (телефон/email)

- **Branch:** `feature/otp-auth`
- **Created:** 2026-03-21

## Description

Переработка регистрации и авторизации. Убираем login, name, surname, password. Пользователь указывает телефон (по умолчанию) или email, получает код верификации и входит/регистрируется по коду. Телефон — через TelegramGateway, email — через Laravel Notification. После регистрации — автоматическая авторизация с remember=true.

## Settings

- **Testing:** yes
- **Logging:** minimal (WARN/ERROR only)
- **Docs:** no (warn-only)

## Tasks

### Phase 1: Данные и домен (Tasks 1-2)

**Task 1 — Миграции** ✅
- Модифицировать users: добавить `phone` (nullable, unique), сделать `login`, `name`, `surname`, `email`, `password` nullable
- Создать таблицу `verification_codes`: id, user_id (nullable FK), identifier, type (phone/email), code, expires_at, verified_at, attempts, timestamps
- Индекс на (identifier, code)

**Task 2 — Доменный слой** ✅
- `app/Domain/User/Enums/ContactType.php` — Phone, Email
- `app/Domain/User/Models/VerificationCode.php` — модель с скоупами
- Exceptions: VerificationCodeExpiredException, VerificationCodeInvalidException, TooManyAttemptsException, UserNotFoundException
- Обновить User модель: phone в fillable

### Phase 2: Бизнес-логика (Tasks 3-5)

**Task 3 — Email Notification** ✅
- `app/Notifications/Auth/SendVerificationCodeNotification.php` — отправка кода на email через очередь

**Task 4 — SendCodeAction + VerifyCodeAction** ✅
- SendCodeAction: генерация кода, отправка через TelegramGateway (phone) или Notification (email), rate limiting 60 сек
- VerifyCodeAction: проверка кода, инкремент attempts, max 5 попыток
- DTOs: SendCodeData, VerifyCodeData

**Task 5 — RegisterWithCodeAction + LoginWithCodeAction** ✅
- RegisterWithCodeAction: верификация кода → создание пользователя (phone/email) → NestedSet ментор → авто-логин remember=true
- LoginWithCodeAction: верификация кода → поиск пользователя → авто-логин remember=true
- DTOs: RegisterWithCodeData, LoginWithCodeData

### Phase 3: HTTP и UI (Tasks 6-8)

**Task 6 — Контроллеры, Form Requests, маршруты** ✅
- RegisterController: showRegistrationForm, sendCode, register
- LoginController: showLoginForm, sendCode, login
- Form Requests: SendCodeRequest, RegisterRequest, LoginRequest
- Маршруты: отключить Fortify login/register, добавить свои
- Обновить FortifyServiceProvider

**Task 7 — Vue страницы** ✅
- Register.vue: табы телефон/email, поле ввода, кнопка "Получить код", таймер, поле кода, кнопка регистрации
- Login.vue: табы телефон/email, поле ввода, кнопка "Получить код", таймер, поле кода, кнопка входа

**Task 8 — Локализация** ✅
- Новые ключи в lang/ru.json и lang/en.json для OTP-потока

### Phase 4: Тесты (Task 9)

**Task 9 — Переписать auth тесты** ✅
- Feature тесты: регистрация и логин через phone/email + коды
- Unit тесты: SendCodeAction, VerifyCodeAction
- Моки: TelegramGateway, Notification

## Commit Plan

### Commit 1 (после Tasks 1-3)
```
feat(auth): миграции, доменный слой и notification для OTP-авторизации
```

### Commit 2 (после Tasks 4-5)
```
feat(auth): actions для отправки/верификации кодов и регистрации/логина
```

### Commit 3 (после Tasks 6-8)
```
feat(auth): контроллеры, Vue страницы и локализация OTP-авторизации
```

### Commit 4 (после Task 9)
```
test(auth): тесты OTP-авторизации
```
