# --- Makefile for Golden Connect ---
# Usage: make [target]

SHELL := bash
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

# --- Project ---
PROJECT  ?= golden-connect
PHP      ?= php
COMPOSER ?= composer
NPM      ?= npm
ARTISAN  := $(PHP) artisan

# --- Git ---
VERSION    ?= $(shell git describe --tags --always --dirty 2>/dev/null || echo "dev")
COMMIT     ?= $(shell git rev-parse --short HEAD 2>/dev/null || echo "unknown")
BUILD_TIME := $(shell date -u '+%Y-%m-%dT%H:%M:%SZ')

# --- Docker ---
COMPOSE         := docker compose

# ============================================================================
.DEFAULT_GOAL := help

##@ Setup

.PHONY: install
install: ## Install PHP and JS dependencies
	$(COMPOSER) install --no-interaction --prefer-dist
	$(NPM) install

.PHONY: setup
setup: ## First-time project setup
	$(COMPOSER) install --no-interaction --prefer-dist
	@test -f .env || cp .env.example .env
	$(ARTISAN) key:generate
	$(NPM) install
	$(NPM) run build
	$(ARTISAN) migrate
	@echo "Setup complete! Run 'make dev' to start."

##@ Development

.PHONY: dev
dev: ## Start dev server (Octane + Queue + Vite + Pail)
	$(COMPOSER) dev

.PHONY: serve
serve: ## Start Octane dev server only
	$(ARTISAN) octane:start --server=swoole --watch

.PHONY: vite
vite: ## Start Vite dev server only
	$(NPM) run dev

.PHONY: build
build: ## Build frontend assets for production
	$(NPM) run build

.PHONY: tinker
tinker: ## Open Laravel Tinker REPL
	$(ARTISAN) tinker

.PHONY: routes
routes: ## List application routes
	$(ARTISAN) route:list

.PHONY: horizon
horizon: ## Start Laravel Horizon
	$(ARTISAN) horizon

.PHONY: reverb
reverb: ## Start Laravel Reverb WebSocket server
	$(ARTISAN) reverb:start

##@ Testing

.PHONY: test
test: ## Run tests (Pest)
	$(ARTISAN) test

.PHONY: test-cover
test-cover: ## Run tests with coverage report
	./vendor/bin/pest --coverage

.PHONY: test-filter
test-filter: ## Run filtered tests (usage: make test-filter FILTER="ClassName")
	./vendor/bin/pest --filter="$(FILTER)"

.PHONY: test-parallel
test-parallel: ## Run tests in parallel
	$(ARTISAN) test --parallel

##@ Code Quality

.PHONY: lint
lint: ## Check code style (Pint dry-run)
	./vendor/bin/pint --test

.PHONY: lint-fix
lint-fix: ## Fix code style issues (Pint)
	./vendor/bin/pint

.PHONY: fmt
fmt: lint-fix ## Alias for lint-fix

.PHONY: ci
ci: lint test ## Run CI pipeline (lint + test)

##@ Database

.PHONY: db-migrate
db-migrate: ## Run database migrations
	$(ARTISAN) migrate

.PHONY: db-rollback
db-rollback: ## Rollback last migration
	$(ARTISAN) migrate:rollback

.PHONY: db-seed
db-seed: ## Seed the database
	$(ARTISAN) db:seed

.PHONY: db-fresh
db-fresh: ## Drop all tables and re-run migrations + seeds (DANGEROUS)
	@echo "WARNING: This will drop ALL tables!"
	@read -p "Are you sure? [y/N] " confirm
	@[[ "$$confirm" == [yY] ]] || exit 1
	$(ARTISAN) migrate:fresh --seed

.PHONY: db-status
db-status: ## Show migration status
	$(ARTISAN) migrate:status

##@ Cache & Optimization

.PHONY: cache-clear
cache-clear: ## Clear all caches
	$(ARTISAN) cache:clear
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear
	$(ARTISAN) event:clear

.PHONY: optimize
optimize: ## Cache config, routes, views, events for production
	$(ARTISAN) config:cache
	$(ARTISAN) route:cache
	$(ARTISAN) view:cache
	$(ARTISAN) event:cache

##@ Docker — Development

.PHONY: docker-dev
docker-dev: ## Start all services in dev mode (including mailpit)
	$(COMPOSE) --profile dev up -d

.PHONY: docker-dev-vite
docker-dev-vite: ## Start all services in dev mode with Vite
	$(COMPOSE) --profile dev --profile vite up -d

.PHONY: docker-dev-build
docker-dev-build: ## Rebuild dev containers
	$(COMPOSE) up -d --build

.PHONY: docker-dev-down
docker-dev-down: ## Stop dev environment
	$(COMPOSE) --profile dev --profile vite down

.PHONY: docker-dev-logs
docker-dev-logs: ## Tail dev logs
	$(COMPOSE) logs -f

.PHONY: docker-shell
docker-shell: ## Open shell in app container
	$(COMPOSE) exec app sh

.PHONY: docker-test
docker-test: ## Run tests inside Docker container
	$(COMPOSE) exec app php artisan test

.PHONY: docker-migrate
docker-migrate: ## Run migrations inside Docker container
	$(COMPOSE) exec app php artisan migrate

##@ Infrastructure

.PHONY: infra-up
infra-up: ## Start only infrastructure (DB + Redis)
	$(COMPOSE) up -d db redis

.PHONY: infra-down
infra-down: ## Stop infrastructure services
	$(COMPOSE) stop db redis

##@ Cleanup

.PHONY: clean
clean: ## Remove generated files and caches
	rm -rf node_modules/.vite
	rm -rf public/build
	rm -rf bootstrap/cache/*.php
	rm -rf storage/framework/cache/data/*
	rm -rf storage/framework/sessions/*
	rm -rf storage/framework/views/*
	rm -rf storage/logs/*.log

##@ Help

.PHONY: help
help: ## Show this help
	@awk 'BEGIN {FS = ":.*##"; printf "Usage:\n  make \033[36m<target>\033[0m\n"} \
		/^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2} \
		/^##@/ {printf "\n\033[1m%s\033[0m\n", substr($$0, 5)}' $(MAKEFILE_LIST)
