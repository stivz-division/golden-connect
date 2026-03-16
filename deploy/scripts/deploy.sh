#!/usr/bin/env bash

# ===================================================================
# Production Deployment Script — Golden Connect
# ===================================================================
# Initial deployment with pre-flight checks, migration, and health
# verification.
#
# Usage: ./deploy/scripts/deploy.sh
# ===================================================================

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
COMPOSE_FILE="${PROJECT_ROOT}/compose.yml"
COMPOSE_PROD="${PROJECT_ROOT}/compose.production.yml"
ENV_FILE="${PROJECT_ROOT}/.env"

log_info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1"; }
error_exit()  { log_error "$1"; exit 1; }

dc() { docker compose -f "${COMPOSE_FILE}" -f "${COMPOSE_PROD}" "$@"; }

# ===================================================================
# Pre-flight Checks
# ===================================================================

log_info "Starting pre-flight checks..."

command -v docker &>/dev/null || error_exit "Docker is not installed"
docker compose version &>/dev/null || error_exit "Docker Compose v2 is not installed"
docker info &>/dev/null || error_exit "Docker daemon is not running"

[ -f "${COMPOSE_FILE}" ] || error_exit "compose.yml not found"
[ -f "${COMPOSE_PROD}" ] || error_exit "compose.production.yml not found"
[ -f "${ENV_FILE}" ] || error_exit ".env not found. Copy .env.example to .env and configure it."

set -a
source "${ENV_FILE}" 2>/dev/null || error_exit "Failed to load .env file"
set +a

REQUIRED_VARS=("DB_PASSWORD" "DB_ROOT_PASSWORD")
for var in "${REQUIRED_VARS[@]}"; do
    [ -z "${!var:-}" ] && error_exit "Required variable $var is not set in .env"
done

log_success "Pre-flight checks passed"

# ===================================================================
# Build
# ===================================================================

cd "${PROJECT_ROOT}"

VERSION="${VERSION:-$(git describe --tags --always --dirty 2>/dev/null || date +%Y%m%d-%H%M%S)}"
export VERSION

# Build or pull images
if [ -n "${DOCKER_IMAGE:-}" ]; then
    log_info "Pulling images from registry (version: ${VERSION})..."
    dc pull app nginx || error_exit "Pull failed"
    log_success "Images pulled successfully"
else
    log_info "Building application image (version: ${VERSION})..."
    dc build || error_exit "Build failed"
    log_success "Images built successfully"
fi

# ===================================================================
# Start Infrastructure
# ===================================================================

log_info "Starting infrastructure services..."
dc up -d db redis 2>/dev/null || true

log_info "Waiting for infrastructure to be ready..."
MAX_WAIT=60
WAIT=0

while [ $WAIT -lt $MAX_WAIT ]; do
    DB_HEALTH=$(dc ps --format '{{.Health}}' db 2>/dev/null || echo "starting")
    REDIS_HEALTH=$(dc ps --format '{{.Health}}' redis 2>/dev/null || echo "starting")

    if [ "$DB_HEALTH" = "healthy" ] && [ "$REDIS_HEALTH" = "healthy" ]; then
        log_success "Infrastructure is ready"
        break
    fi

    [ $((WAIT % 10)) -eq 0 ] && log_info "Waiting... (${WAIT}s / ${MAX_WAIT}s)"
    sleep 1
    WAIT=$((WAIT + 1))
done

[ $WAIT -ge $MAX_WAIT ] && error_exit "Infrastructure failed to become healthy within ${MAX_WAIT}s"

# ===================================================================
# Migrations
# ===================================================================

log_info "Running database migrations..."
dc run --rm app php artisan migrate --force || error_exit "Migrations failed"
log_success "Migrations completed"

# ===================================================================
# Start Application
# ===================================================================

log_info "Starting all services..."
dc up -d || error_exit "Failed to start application"

# Health verification
log_info "Verifying deployment health..."
MAX_WAIT=60
WAIT=0

while [ $WAIT -lt $MAX_WAIT ]; do
    HEALTH=$(dc ps --format '{{.Health}}' app 2>/dev/null || echo "starting")
    if [ "$HEALTH" = "healthy" ]; then
        log_success "Application is healthy"
        break
    fi
    [ $((WAIT % 10)) -eq 0 ] && log_info "Waiting for health check... (${WAIT}s / ${MAX_WAIT}s)"
    sleep 1
    WAIT=$((WAIT + 1))
done

if [ $WAIT -ge $MAX_WAIT ]; then
    log_error "Application failed health check"
    dc logs --tail=30
    error_exit "Deployment failed"
fi

# ===================================================================
# Summary
# ===================================================================

log_success "════════════════════════════════════════════"
log_success "  Deployment completed successfully!"
log_success "════════════════════════════════════════════"

echo ""
log_info "Service Status:"
dc ps

echo ""
log_info "Useful Commands:"
echo "  View logs:      ./deploy/scripts/logs.sh"
echo "  Health check:   ./deploy/scripts/health-check.sh"
echo "  Update:         ./deploy/scripts/update.sh"
echo "  Rollback:       ./deploy/scripts/rollback.sh"
