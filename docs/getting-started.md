[Back to README](../README.md) · [Architecture →](architecture.md)

# Getting Started

## Prerequisites

- PHP 8.2+ (рекомендуется 8.4)
- Composer 2
- Node.js 22+
- MySQL 8.4
- Redis 7+
- Docker & Docker Compose (для контейнерного запуска)

## Installation (Docker)

```bash
# Клонировать репозиторий
git clone <repo-url> golden-connect
cd golden-connect

# Скопировать конфигурацию
cp .env.example .env

# Запустить все сервисы
docker compose up -d

# Выполнить миграции
docker compose exec app php artisan migrate

# Открыть в браузере
open http://localhost
```

## Installation (Local)

```bash
# Установить зависимости
composer install
npm install

# Настроить окружение
cp .env.example .env
php artisan key:generate

# Настроить БД в .env
# DB_HOST=127.0.0.1
# DB_DATABASE=golden_connect
# DB_USERNAME=root
# DB_PASSWORD=

# Запустить миграции
php artisan migrate

# Запустить dev-сервер (Octane + Queue + Vite + Pail)
composer dev
```

## Verify Installation

После запуска проверьте:

| Компонент | URL / Команда | Ожидаемый результат |
|-----------|---------------|---------------------|
| Приложение | `http://localhost` (Docker) или `http://localhost:8000` (local) | Главная страница |
| Horizon | `http://localhost/horizon` | Dashboard очередей |
| База данных | `php artisan db:show` | Информация о подключении |

## Running Tests

```bash
# Все тесты
php artisan test

# Или через Pest напрямую
./vendor/bin/pest

# С покрытием
./vendor/bin/pest --coverage
```

## Next Steps

- [Architecture](architecture.md) — понимание структуры проекта и DDD
- [Configuration](configuration.md) — настройка переменных окружения
- [Deployment](deployment.md) — запуск в production

## See Also

- [Architecture](architecture.md) — структура проекта и bounded contexts
- [Configuration](configuration.md) — все переменные окружения
