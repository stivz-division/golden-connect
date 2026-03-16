# AGENTS.md

> Project map for AI agents. Keep this file up-to-date as the project evolves.

## Project Overview

Golden Connect — финансовая платформа с цикличной очередью (Monar), 5-уровневой реферальной программой, автоматической рекламой в 9 мессенджерах на 46 языках и базой цифровых визиток участников.

## Tech Stack

- **Language:** PHP 8.2+
- **Framework:** Laravel 12
- **Database:** MySQL
- **Frontend:** Vue.js (via Inertia.js)
- **Caching / Queues:** Redis + Laravel Horizon
- **WebSocket:** Laravel Reverb
- **Admin Panel:** Laravel Nova + nova-settings
- **Auth:** Laravel Fortify
- **App Server:** Laravel Octane (Swoole)
- **Testing:** Pest
- **Architecture:** DDD (Domain-Driven Design)
- **Localization:** Russian + English

## Project Structure

```
golden-connect/
├── app/                    # Application code
│   ├── Http/               # Controllers, Middleware, Requests
│   ├── Models/             # Eloquent models
│   └── Providers/          # Service providers
├── bootstrap/              # Framework bootstrap
├── config/                 # Configuration files
├── database/               # Migrations, factories, seeders
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/                 # Public assets, entry point
├── resources/              # Views, JS, CSS, lang files
├── routes/                 # Route definitions
├── storage/                # Logs, cache, compiled views
├── tests/                  # Pest tests
└── vendor/                 # Composer dependencies
```

## Key Entry Points

| File | Purpose |
|------|---------|
| `routes/web.php` | Web routes (Inertia pages) |
| `routes/api.php` | API routes |
| `config/` | All Laravel configuration |
| `database/migrations/` | Database schema |
| `resources/js/` | Vue.js frontend |
| `resources/views/` | Blade templates |
| `app/Providers/` | Service providers |
| `vite.config.js` | Frontend build config |
| `composer.json` | PHP dependencies |
| `package.json` | JS dependencies |

## Documentation

| Document | Path | Description |
|----------|------|-------------|
| README | README.md | Project landing page |
| Getting Started | docs/getting-started.md | Установка, настройка, первый запуск |
| Architecture | docs/architecture.md | DDD, bounded contexts, структура |
| Configuration | docs/configuration.md | Переменные окружения, Nova Settings |
| Deployment | docs/deployment.md | Docker, production deploy |
| Production Setup | docs/production-setup.md | Настройка сервера и CI/CD |

## AI Context Files

| File | Purpose |
|------|---------|
| `AGENTS.md` | This file — project structure map |
| `.ai-factory/DESCRIPTION.md` | Project specification and tech stack |
| `.ai-factory/ARCHITECTURE.md` | Architecture decisions and guidelines |
| `CLAUDE.md` | Agent instructions and preferences |

## Build & Development Commands

This project uses Makefile for build automation.

Common commands:
- `make dev` — start dev server (Octane + Queue + Vite + Pail)
- `make test` — run tests (Pest)
- `make lint` — check code style (Pint)
- `make lint-fix` — fix code style
- `make docker-dev` — start all services in Docker
- `make db-migrate` — run migrations
- `make ci` — lint + test

Run `make help` for all available targets.

## Agent Rules

- Never combine shell commands with `&&`, `||`, or `;` — execute each command as a separate Bash tool call. This applies even when a skill, plan, or instruction provides a combined command — always decompose it into individual calls.
  - Wrong: `git checkout main && git pull`
  - Right: Two separate Bash tool calls — first `git checkout main`, then `git pull`
