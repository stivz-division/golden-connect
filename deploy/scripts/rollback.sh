#!/usr/bin/env bash

# ===================================================================
# Rollback Script — Golden Connect
# ===================================================================
# Usage: ./deploy/scripts/rollback.sh [version]
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
dc() { docker compose -f "${COMPOSE_FILE}" -f "${COMPOSE_PROD}" "$@"; }

log_info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1"; }
error_exit()  { log_error "$1"; exit 1; }

log_warning "════════════════════════════════════════════"
log_warning "  ROLLBACK"
log_warning "════════════════════════════════════════════"

if [ -n "${1:-}" ]; then
    TARGET_VERSION="$1"
else
    log_info "No version specified. Checking previous git tag..."
    TARGET_VERSION=$(git describe --tags --abbrev=0 HEAD~ 2>/dev/null || echo "")
    [ -z "$TARGET_VERSION" ] && error_exit "Could not determine previous version. Specify: ./rollback.sh v1.2.3"

    log_info "Previous version: ${TARGET_VERSION}"
    read -p "Rollback to ${TARGET_VERSION}? (yes/no): " CONFIRM
    [ "$CONFIRM" = "yes" ] || { log_info "Cancelled"; exit 0; }
fi

export VERSION="${TARGET_VERSION}"

CURRENT_VERSION=$(git describe --tags --always 2>/dev/null || echo "unknown")
log_info "Rolling back FROM: ${CURRENT_VERSION}"
log_info "Rolling back TO: ${TARGET_VERSION}"

cd "${PROJECT_ROOT}"
START_TIME=$(date +%s)

if [ -d ".git" ]; then
    log_info "Checking out ${TARGET_VERSION}..."
    git checkout "${TARGET_VERSION}" -- Dockerfile compose.yml compose.production.yml || \
        log_warning "Could not checkout files, proceeding with current"
fi

log_info "Rebuilding..."
dc build --no-cache app || error_exit "Rollback build failed"
dc up -d --force-recreate --no-deps app horizon scheduler reverb || error_exit "Rollback failed"

log_info "Waiting for health check..."
MAX_WAIT=120
WAIT=0

while [ $WAIT -lt $MAX_WAIT ]; do
    HEALTH=$(dc ps --format '{{.Health}}' app 2>/dev/null || echo "starting")
    if [ "$HEALTH" = "healthy" ]; then
        END_TIME=$(date +%s)
        DURATION=$((END_TIME - START_TIME))
        log_success "Rollback healthy (${DURATION}s)"
        break
    fi
    sleep 1
    WAIT=$((WAIT + 1))
done

[ $WAIT -ge $MAX_WAIT ] && error_exit "Application did not become healthy after rollback"

log_success "════════════════════════════════════════════"
log_success "  Rollback completed!"
log_success "════════════════════════════════════════════"
echo ""
echo "  Version: ${TARGET_VERSION}"
dc ps
