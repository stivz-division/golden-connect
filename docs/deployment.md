[← Configuration](configuration.md) · [Back to README](../README.md) · [Production Setup →](production-setup.md)

# Deployment

## Docker Architecture

```
                    ┌─────────┐
                    │  Nginx  │ :80/:443
                    └────┬────┘
                         │
          ┌──────────────┼──────────────┐
          │              │              │
     ┌────┴────┐   ┌─────┴─────┐  ┌────┴────┐
     │   App   │   │  Reverb   │  │ Horizon │
     │ (Octane)│   │ (WebSocket│  │ (Queue) │
     │  :8000  │   │   :8080)  │  │         │
     └────┬────┘   └─────┬─────┘  └────┬────┘
          │              │              │
     ┌────┴──────────────┴──────────────┴────┐
     │            Backend Network            │
     │  ┌─────────┐          ┌─────────┐     │
     │  │  MySQL  │          │  Redis  │     │
     │  │  :3306  │          │  :6379  │     │
     │  └─────────┘          └─────────┘     │
     └───────────────────────────────────────┘
```

## Services

| Service | Image | Purpose |
|---------|-------|---------|
| `app` | PHP 8.4 + Swoole | Laravel Octane — основное приложение |
| `horizon` | PHP 8.4 + Swoole | Laravel Horizon — обработка очередей |
| `scheduler` | PHP 8.4 + Swoole | Laravel Scheduler — планировщик задач |
| `reverb` | PHP 8.4 + Swoole | Laravel Reverb — WebSocket сервер |
| `nginx` | Nginx 1.27 | Reverse proxy, статика |
| `db` | MySQL 8.4 | База данных |
| `redis` | Redis 7 | Кэш, сессии, очереди |

## Development

```bash
# Запустить все сервисы (dev mode)
docker compose up -d

# С Mailpit (для тестирования email)
docker compose --profile dev up -d

# Логи
docker compose logs -f app

# Выполнить команду в контейнере
docker compose exec app php artisan <command>
```

## Production Deployment

### Initial Deploy

```bash
# 1. Настроить окружение
cp .env.example .env
# Отредактировать .env: установить APP_ENV=production, DB_PASSWORD, etc.

# 2. Запустить
./deploy/scripts/deploy.sh
```

### Update

```bash
./deploy/scripts/update.sh [version]
```

### Other Operations

| Скрипт | Назначение |
|--------|------------|
| `deploy/scripts/deploy.sh` | Первичный деплой |
| `deploy/scripts/update.sh` | Обновление с zero-downtime |
| `deploy/scripts/logs.sh` | Просмотр логов |
| `deploy/scripts/health-check.sh` | Проверка здоровья сервисов |
| `deploy/scripts/rollback.sh` | Откат к предыдущей версии |
| `deploy/scripts/backup.sh` | Бэкап MySQL с ротацией |

## Production Compose

```bash
# Запуск с production-конфигурацией
docker compose -f compose.yml -f compose.production.yml up -d
```

Production overlay включает:
- `read_only: true` на всех сервисах
- `cap_drop: [ALL]` + минимальные `cap_add`
- Resource limits (CPU, memory, PIDs)
- Log rotation
- `restart: unless-stopped`
- Internal backend network

## See Also

- [Getting Started](getting-started.md) — локальная установка
- [Configuration](configuration.md) — переменные окружения
