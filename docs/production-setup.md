[← Deployment](deployment.md) · [Back to README](../README.md)

# Production Setup — Полная инструкция

Пошаговая инструкция для настройки production-сервера и автоматического деплоя через GitHub Actions.

## Схема деплоя

```
  Push в main
       │
       ▼
  GitHub Actions
  ┌──────────────────────────┐
  │ 1. Lint (Pint)           │
  │ 2. Tests (Pest + MySQL)  │  ← Параллельно
  │ 3. Build (Vite)          │
  └──────────┬───────────────┘
             │ Все прошли ✓
             ▼
  Deploy workflow
  ┌──────────────────────────┐
  │ 1. Build Docker image    │
  │ 2. Push в GHCR           │
  │ 3. SSH на сервер         │
  │ 4. Pull image + restart  │
  │ 5. Health check          │
  └──────────────────────────┘
```

---

## 1. Требования к серверу

| Компонент | Минимум | Рекомендуется |
|-----------|---------|---------------|
| OS | Ubuntu 22.04+ / Debian 12+ | Ubuntu 24.04 |
| CPU | 2 cores | 4 cores |
| RAM | 4 GB | 8 GB |
| Disk | 40 GB SSD | 80 GB SSD |
| Docker | 24+ | Последняя стабильная |
| Docker Compose | v2+ | Встроен в Docker |

---

## 2. Настройка сервера

### 2.1 Установить Docker

```bash
# Обновить систему
sudo apt update && sudo apt upgrade -y

# Установить Docker (официальный скрипт)
curl -fsSL https://get.docker.com | sh

# Добавить пользователя в группу docker
sudo usermod -aG docker $USER

# Перелогиниться чтобы группа применилась
exit
# Заново подключиться по SSH
```

### 2.2 Создать пользователя для деплоя

```bash
# Создать пользователя deploy
sudo adduser --disabled-password deploy

# Добавить в docker группу
sudo usermod -aG docker deploy

# Переключиться на пользователя
sudo su - deploy
```

### 2.3 Настроить SSH-ключ для GitHub Actions

На сервере (под пользователем `deploy`):

```bash
# Создать директорию .ssh
mkdir -p ~/.ssh
chmod 700 ~/.ssh
```

На вашем локальном компьютере:

```bash
# Сгенерировать отдельный ключ для деплоя
ssh-keygen -t ed25519 -f ~/.ssh/golden-connect-deploy -C "github-actions-deploy" -N ""

# Скопировать публичный ключ на сервер
ssh-copy-id -i ~/.ssh/golden-connect-deploy.pub deploy@ВАШ_СЕРВЕР
```

Сохраните содержимое **приватного ключа** — он понадобится в GitHub Secrets:

```bash
cat ~/.ssh/golden-connect-deploy
```

### 2.4 Настроить Firewall

```bash
# Разрешить SSH
sudo ufw allow 22/tcp

# Разрешить HTTP и HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Включить firewall
sudo ufw enable
```

---

## 3. Клонировать проект на сервер

Под пользователем `deploy`:

```bash
cd /home/deploy

# Клонировать репозиторий
git clone https://github.com/ВАШ_АККАУНТ/golden-connect.git
cd golden-connect
```

### 3.1 Настроить .env

```bash
cp .env.example .env
```

Отредактировать `.env` — установить production-значения:

```dotenv
# === Application ===
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ваш-домен.com
LOG_LEVEL=warning

# === Database ===
DB_PASSWORD=НАДЁЖНЫЙ_ПАРОЛЬ_ЗДЕСЬ
DB_ROOT_PASSWORD=НАДЁЖНЫЙ_ROOT_ПАРОЛЬ_ЗДЕСЬ

# === Docker Registry ===
DOCKER_REGISTRY=ghcr.io
DOCKER_IMAGE=ваш-аккаунт/golden-connect
VERSION=latest

# === Redis ===
REDIS_PASSWORD=НАДЁЖНЫЙ_ПАРОЛЬ_REDIS

# === Reverb ===
REVERB_APP_SECRET=СЛУЧАЙНАЯ_СТРОКА_32_СИМВОЛА

# === Mail (production SMTP) ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.ваш-провайдер.com
MAIL_PORT=587
MAIL_USERNAME=ваш-email
MAIL_PASSWORD=ваш-пароль
```

### 3.2 Авторизовать Docker в GHCR

На сервере нужно авторизовать Docker для скачивания образов из GitHub Container Registry:

```bash
# Создайте Personal Access Token на GitHub:
#   Settings → Developer settings → Personal access tokens → Tokens (classic)
#   Scope: read:packages

echo "ВАШ_GITHUB_TOKEN" | docker login ghcr.io -u ВАШ_GITHUB_USERNAME --password-stdin
```

### 3.3 Первичный деплой

```bash
# Выполнить первый деплой
./deploy/scripts/deploy.sh
```

Скрипт сам соберёт образ, запустит инфраструктуру, выполнит миграции и стартует приложение.

---

## 4. Настроить GitHub Actions

### 4.1 Создать Environment

В репозитории на GitHub:

1. **Settings** → **Environments** → **New environment**
2. Имя: `production`
3. (Опционально) Включить **Required reviewers** — деплой будет ждать подтверждения

### 4.2 Добавить Secrets

**Settings** → **Secrets and variables** → **Actions** → **New repository secret**

| Secret | Значение | Описание |
|--------|----------|----------|
| `SERVER_HOST` | `123.456.789.0` | IP-адрес вашего сервера |
| `SERVER_USER` | `deploy` | Имя пользователя на сервере |
| `SERVER_SSH_KEY` | `-----BEGIN OPENSSH PRIVATE KEY-----...` | Приватный SSH-ключ (весь файл целиком) |
| `SERVER_PORT` | `22` | SSH-порт (если отличается от 22) |
| `SERVER_PROJECT_PATH` | `/home/deploy/golden-connect` | Путь к проекту на сервере |

> `GITHUB_TOKEN` не нужно добавлять — он предоставляется автоматически.

### 4.3 Проверить что всё работает

```bash
# Ручной запуск deploy workflow
gh workflow run deploy.yml
```

Или в GitHub UI: **Actions** → **Deploy** → **Run workflow**

---

## 5. Как работает деплой

### Автоматический (при push в main)

1. Push в `main` запускает **Lint**, **Tests**, **Build** параллельно
2. Когда все 3 прошли — запускается **Deploy**
3. Deploy собирает Docker-образ, пушит в GHCR, заходит на сервер по SSH
4. На сервере: `git pull` → `docker pull` → миграции → rolling restart
5. Ждёт health check (60 секунд)

### Ручной

Можно запустить деплой вручную из GitHub Actions UI (кнопка "Run workflow"), минуя ожидание тестов.

### Откат

Если деплой сломал production:

```bash
# На сервере
cd /home/deploy/golden-connect
./deploy/scripts/rollback.sh
```

---

## 6. SSL-сертификат (HTTPS)

### Вариант A: Certbot (Let's Encrypt)

```bash
# Установить certbot
sudo apt install certbot

# Получить сертификат (nginx остановить на время получения)
docker compose -f compose.yml -f compose.production.yml stop nginx
sudo certbot certonly --standalone -d ваш-домен.com
docker compose -f compose.yml -f compose.production.yml start nginx
```

Добавить в `docker/nginx/conf.d/default.conf`:

```nginx
server {
    listen 443 ssl;
    server_name ваш-домен.com;

    ssl_certificate /etc/letsencrypt/live/ваш-домен.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ваш-домен.com/privkey.pem;

    # ... остальная конфигурация
}
```

Добавить volume в `compose.yml` для nginx:

```yaml
nginx:
  volumes:
    - /etc/letsencrypt:/etc/letsencrypt:ro
```

### Вариант B: Cloudflare Proxy

1. Добавить домен в Cloudflare
2. Включить **Proxied** (оранжевое облако) для A-записи
3. SSL/TLS → **Full (Strict)**
4. Cloudflare обеспечит HTTPS автоматически

---

## 7. Мониторинг

### Health Check (cron)

```bash
# Добавить в crontab пользователя deploy
crontab -e

# Каждые 5 минут проверять здоровье
*/5 * * * * /home/deploy/golden-connect/deploy/scripts/health-check.sh >> /home/deploy/health.log 2>&1
```

### Backup (cron)

```bash
# Ежедневный бэкап в 3:00
0 3 * * * /home/deploy/golden-connect/deploy/scripts/backup.sh >> /home/deploy/backup.log 2>&1
```

---

## Чеклист перед первым деплоем

- [ ] Сервер настроен (Docker, firewall, пользователь `deploy`)
- [ ] SSH-ключ для GitHub Actions создан и добавлен на сервер
- [ ] Репозиторий склонирован на сервер
- [ ] `.env` настроен с production-значениями
- [ ] Docker авторизован в GHCR на сервере
- [ ] GitHub Environment `production` создан
- [ ] GitHub Secrets добавлены (5 штук)
- [ ] Первичный деплой выполнен (`./deploy/scripts/deploy.sh`)
- [ ] SSL-сертификат настроен
- [ ] Cron для health check и backup добавлен
- [ ] DNS A-запись указывает на сервер

## See Also

- [Deployment](deployment.md) — Docker-архитектура и скрипты
- [Configuration](configuration.md) — Переменные окружения
