[← Architecture](architecture.md) · [Back to README](../README.md) · [Deployment →](deployment.md)

# Configuration

## Environment Variables

### Application

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | `Golden Connect` | Название приложения |
| `APP_ENV` | `local` | Окружение: `local`, `production` |
| `APP_DEBUG` | `true` | Режим отладки |
| `APP_URL` | `http://localhost` | Базовый URL |
| `APP_LOCALE` | `en` | Язык по умолчанию (`ru`, `en`) |

### Database (MySQL)

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_CONNECTION` | `mysql` | Драйвер БД |
| `DB_HOST` | `db` (Docker) / `127.0.0.1` | Хост MySQL |
| `DB_PORT` | `3306` | Порт MySQL |
| `DB_DATABASE` | `golden_connect` | Имя базы данных |
| `DB_USERNAME` | `app` | Пользователь MySQL |
| `DB_PASSWORD` | — | Пароль MySQL (обязательный) |
| `DB_ROOT_PASSWORD` | — | Root-пароль MySQL (Docker) |

### Redis

| Variable | Default | Description |
|----------|---------|-------------|
| `REDIS_HOST` | `redis` (Docker) / `127.0.0.1` | Хост Redis |
| `REDIS_PORT` | `6379` | Порт Redis |
| `REDIS_PASSWORD` | `null` | Пароль Redis |

### Octane

| Variable | Default | Description |
|----------|---------|-------------|
| `OCTANE_SERVER` | `swoole` | Сервер Octane (`swoole`, `roadrunner`) |

### Reverb (WebSocket)

| Variable | Default | Description |
|----------|---------|-------------|
| `REVERB_APP_ID` | — | ID приложения Reverb |
| `REVERB_APP_KEY` | — | Ключ приложения |
| `REVERB_APP_SECRET` | — | Секрет приложения |
| `REVERB_HOST` | `reverb` | Хост WebSocket сервера |
| `REVERB_PORT` | `8080` | Порт WebSocket сервера |

### Mail

| Variable | Default | Description |
|----------|---------|-------------|
| `MAIL_MAILER` | `smtp` | Драйвер отправки (`smtp`, `log`) |
| `MAIL_HOST` | `mailpit` (dev) | SMTP хост |
| `MAIL_PORT` | `1025` (dev) | SMTP порт |

## Nova Settings

Настраиваемые бизнес-параметры через `nova_get_setting()`:

```php
// Получение значения
$link = nova_get_setting('support_chat_link', 'https://t.me/default');

// Регистрация в NovaServiceProvider
Text::make('Ссылка на чат поддержки', 'support_chat_link'),
```

Все изменяемые бизнес-параметры (проценты пулов, ставки, лимиты) должны быть зарегистрированы в `NovaServiceProvider` как Nova Settings.

## Localization

Проект поддерживает два языка для UI:

| Язык | Код | Файлы |
|------|-----|-------|
| Русский | `ru` | `resources/lang/ru/` |
| Английский | `en` | `resources/lang/en/` |

Весь пользовательский текст — через `__()` / `trans()`.

Для рекламного контента поддерживается 46 языков с автоматическим переводом.

## See Also

- [Architecture](architecture.md) — структура проекта
- [Deployment](deployment.md) — production настройка
