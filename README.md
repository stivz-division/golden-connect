# Golden Connect

> Финансовая платформа с цикличной очередью, реферальной программой и автоматической рекламой в мессенджерах.

Участники приобретают лоты ($50–$1,000), получают рабочие места в цикличной очереди Monar и зарабатывают +100% от суммы лота. Платформа автоматически публикует рекламу на 46 языках в 9 мессенджерах и предоставляет цифровые визитки с нетворкингом.

## Quick Start

```bash
# Клонировать репозиторий
git clone <repo-url> golden-connect
cd golden-connect

# Запустить через Docker
cp .env.example .env
docker compose up -d

# Или локально
composer install
npm install
php artisan migrate
composer dev
```

## Key Features

- **Cyclic Monar** — цикличная очередь с параллельными рабочими местами и автоматическим реинвестом
- **3 баланса** — баланс пополнения, дохода и реферальный с полной историей операций
- **Мировой пул** — ежемесячное распределение между участниками с лотами от $300
- **5-уровневая реферальная система** — линейная классическая с деревом приглашений
- **Автоматическая реклама** — публикация в 9 мессенджерах на 46 языках с модерацией
- **Цифровые визитки** — профиль участника с авто-переводом и QR-кодом
- **BEP20** — пополнение и вывод через сеть BSC

## Tech Stack

PHP 8.4 / Laravel 12 / MySQL / Vue.js + Inertia.js / Redis / Laravel Horizon / Octane (Swoole) / Reverb / Nova / Docker

---

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Установка, настройка, первый запуск |
| [Architecture](docs/architecture.md) | DDD, bounded contexts, структура проекта |
| [Configuration](docs/configuration.md) | Переменные окружения, Nova Settings |
| [Deployment](docs/deployment.md) | Docker, Octane, production deploy |
| [Production Setup](docs/production-setup.md) | Настройка сервера и CI/CD |

## Quick Commands

| Command          | Description                                       |
|------------------|---------------------------------------------------|
| `make dev`       | Запустить dev-сервер (Octane + Queue + Vite + Pail) |
| `make test`      | Запустить тесты (Pest)                            |
| `make lint`      | Проверить код стиль (Pint)                        |
| `make docker-dev`| Запустить все сервисы в Docker                    |
| `make db-migrate`| Выполнить миграции                                |
| `make ci`        | Линтер + тесты                                    |

Полный список: `make help`

## License

Proprietary
