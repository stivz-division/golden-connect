[← Deployment](deployment.md) · [Back to README](../README.md) · [Localization →](localization.md)

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

**Зачем это нужно:** GitHub Actions будет подключаться к вашему серверу по SSH, чтобы деплоить код. Для этого нужна пара ключей: публичный ключ кладём на сервер («замок»), приватный ключ кладём в GitHub Secrets («ключ от замка»).

#### Шаг 1 — Создать ключ НА СЕРВЕРЕ

Подключитесь к серверу по SSH под пользователем `deploy`:

```bash
ssh deploy@103.75.127.131
```

Сгенерируйте SSH-ключ прямо на сервере:

```bash
# Создать папку для ключей (если ещё нет)
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Сгенерировать ключ (просто нажмите Enter на все вопросы)
ssh-keygen -t ed25519 -f ~/.ssh/github-actions -C "github-actions-deploy" -N ""
```

После этого появятся два файла:
- `~/.ssh/github-actions` — **приватный** ключ (секретный, пойдёт в GitHub)
- `~/.ssh/github-actions.pub` — **публичный** ключ (останется на сервере)

#### Шаг 2 — Разрешить вход по этому ключу НА СЕРВЕРЕ

Всё ещё на сервере — добавьте публичный ключ в список разрешённых:

```bash
cat ~/.ssh/github-actions.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

Теперь сервер будет принимать SSH-подключения с этим ключом.

#### Шаг 3 — Скопировать ПРИВАТНЫЙ ключ для GitHub

Всё ещё на сервере — выведите приватный ключ на экран:

```bash
cat ~/.ssh/github-actions
```

Вы увидите что-то вроде:

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAE...
...много строк...
-----END OPENSSH PRIVATE KEY-----
```

**Скопируйте ВСЁ целиком** (включая строки `-----BEGIN...` и `-----END...`).

#### Шаг 4 — Добавить ключ в GitHub Secrets

1. Откройте репозиторий на GitHub в браузере
2. **Settings** → **Secrets and variables** → **Actions**
3. Нажмите **New repository secret**
4. Name: `SERVER_SSH_KEY`
5. Secret: **вставьте скопированный приватный ключ**
6. Нажмите **Add secret**

Готово! Теперь GitHub Actions сможет подключаться к серверу.

> **Важно:** Приватный ключ — это как пароль. Никогда не публикуйте его, не отправляйте в чат и не коммитьте в git.

### 2.4 Настроить Firewall

**Зачем:** Firewall закрывает все порты кроме нужных. Без него любой сервис на сервере доступен из интернета.

Подключитесь к серверу под **root** или пользователем с `sudo`:

```bash
ssh root@103.75.127.131
```

#### Шаг 1 — Разрешить нужные порты

```bash
# Разрешить SSH (чтобы не потерять доступ!)
sudo ufw allow 22/tcp

# Разрешить HTTP (сайт)
sudo ufw allow 80/tcp

# Разрешить HTTPS (сайт с SSL)
sudo ufw allow 443/tcp
```

#### Шаг 2 — Включить firewall

```bash
sudo ufw enable
```

Он спросит `Command may disrupt existing SSH connections. Proceed with operation (y|n)?` — нажмите `y`.

#### Шаг 3 — Проверить

```bash
sudo ufw status
```

Должно показать:

```
Status: active

To                         Action      From
--                         ------      ----
22/tcp                     ALLOW       Anywhere
80/tcp                     ALLOW       Anywhere
443/tcp                    ALLOW       Anywhere
```

---

## 3. Клонировать проект на сервер

### 3.1 Склонировать репозиторий

Подключитесь к серверу под пользователем `deploy`:

```bash
ssh deploy@103.75.127.131
```

Склонируйте проект:

```bash
cd /home/deploy

# Замените URL на ваш репозиторий
git clone https://github.com/ВАШ_АККАУНТ/golden-connect.git
cd golden-connect
```

> Если репозиторий приватный, GitHub спросит логин и пароль. В качестве пароля используйте **Personal Access Token** (как его создать — описано в шаге 3.3).

### 3.2 Настроить .env

#### Шаг 1 — Скопировать шаблон

```bash
cp .env.example .env
```

#### Шаг 2 — Открыть файл в редакторе

```bash
nano .env
```

> В `nano`: редактируйте текст стрелками, сохранить — `Ctrl+O` → `Enter`, выйти — `Ctrl+X`.

#### Шаг 3 — Заполнить значения

Найдите и замените следующие строки (остальное оставьте как есть):

```dotenv
# === Обязательно заменить ===
APP_ENV=production
APP_DEBUG=false
APP_URL=http://103.75.127.131            # ваш IP или домен
LOG_LEVEL=warning

DB_PASSWORD=Придумайте_сложный_пароль_1   # пароль пользователя БД
DB_ROOT_PASSWORD=Придумайте_сложный_пароль_2  # пароль root БД

# === Если планируете автодеплой через GitHub Actions ===
DOCKER_REGISTRY=ghcr.io
DOCKER_IMAGE=ваш-аккаунт/golden-connect
VERSION=latest
```

> **Как придумать пароль:** на сервере выполните `openssl rand -base64 24` — получите случайную строку. Сделайте это дважды для двух паролей.

#### Шаг 4 — Сгенерировать APP_KEY

```bash
# APP_KEY сгенерируется автоматически при первом деплое,
# но можно сделать это вручную:
php artisan key:generate 2>/dev/null || echo "Ключ сгенерируется при деплое"
```

### 3.3 Авторизовать Docker в GHCR

**Зачем:** Если вы будете использовать GitHub Actions для автодеплоя, Docker на сервере должен уметь скачивать собранные образы из GitHub Container Registry (GHCR).

> **Если вы НЕ планируете автодеплой через GitHub Actions** (а будете делать `git pull` + `make deploy` вручную) — **пропустите этот шаг**.

#### Шаг 1 — Создать Personal Access Token на GitHub

1. Откройте GitHub в браузере
2. Нажмите на свою аватарку (правый верхний угол) → **Settings**
3. Прокрутите вниз в левом меню → **Developer settings**
4. **Personal access tokens** → **Tokens (classic)** → **Generate new token (classic)**
5. Note: `golden-connect-server`
6. Expiration: выберите срок (или **No expiration** для бессрочного)
7. Поставьте галочку на **read:packages**
8. Нажмите **Generate token**
9. **Скопируйте токен** (он показывается только один раз!)

#### Шаг 2 — Авторизовать Docker на сервере

На сервере (под пользователем `deploy`):

```bash
# Замените ВАШ_ТОКЕН и ВАШ_ЛОГИН на реальные значения
echo "ВАШ_ТОКЕН" | docker login ghcr.io -u ВАШ_ЛОГИН_GITHUB --password-stdin
```

Должно появиться: `Login Succeeded`

### 3.4 Первичный деплой

Всё готово! Запускаем:

```bash
cd /home/deploy/golden-connect
./deploy/scripts/deploy.sh
```

**Что произойдёт автоматически:**
1. Проверит что Docker работает и `.env` заполнен
2. Соберёт Docker-образы (это займёт 5-10 минут в первый раз)
3. Запустит базу данных (MySQL) и Redis
4. Подождёт пока они станут healthy
5. Выполнит миграции базы данных
6. Запустит все сервисы (app, nginx, horizon, reverb, scheduler)
7. Проверит что приложение отвечает

В конце увидите:

```
[SUCCESS] ════════════════════════════════════════════
[SUCCESS]   Deployment completed successfully!
[SUCCESS] ════════════════════════════════════════════
```

Откройте в браузере `http://103.75.127.131` — должен показаться сайт.

> **Если что-то пошло не так:** посмотрите логи — `docker compose -f compose.yml -f compose.production.yml logs --tail=50`

---

## 4. Настроить GitHub Actions (автодеплой)

**Зачем:** Чтобы при каждом пуше в `main` сайт обновлялся автоматически — без захода на сервер.

> Если вы предпочитаете обновлять вручную (`git pull` + `make deploy`) — **пропустите этот раздел**.

### 4.1 Создать Environment

**Зачем:** Environment — это «среда» в GitHub. Можно настроить так, чтобы деплой требовал ручного подтверждения (защита от случайного деплоя).

1. Откройте репозиторий на GitHub в браузере
2. **Settings** (вкладка сверху) → **Environments** (в левом меню)
3. Нажмите **New environment**
4. Введите имя: `production`
5. Нажмите **Configure environment**
6. (Необязательно) Поставьте галочку **Required reviewers** и добавьте себя — тогда перед каждым деплоем нужно будет нажать «Approve» в интерфейсе GitHub
7. Нажмите **Save protection rules**

### 4.2 Добавить Secrets

**Зачем:** Secrets — это секретные переменные, которые GitHub Actions использует для подключения к серверу. Они зашифрованы и не видны никому.

1. Откройте репозиторий на GitHub в браузере
2. **Settings** → **Secrets and variables** → **Actions**
3. Нажмите **New repository secret**

Добавьте **5 секретов**, по одному:

**Секрет 1:**
- Name: `SERVER_HOST`
- Secret: `103.75.127.131`
- Нажмите **Add secret**

**Секрет 2:**
- Name: `SERVER_USER`
- Secret: `deploy`
- Нажмите **Add secret**

**Секрет 3:**
- Name: `SERVER_SSH_KEY`
- Secret: содержимое приватного ключа (из шага 2.3, шаг 3)
- Нажмите **Add secret**

**Секрет 4:**
- Name: `SERVER_PORT`
- Secret: `22`
- Нажмите **Add secret**

**Секрет 5:**
- Name: `SERVER_PROJECT_PATH`
- Secret: `/home/deploy/golden-connect`
- Нажмите **Add secret**

### 4.3 Проверить что всё работает

#### Вариант A — через терминал (если установлен `gh` CLI)

```bash
gh workflow run deploy.yml
```

#### Вариант B — через браузер

1. Откройте репозиторий на GitHub
2. Вкладка **Actions** (сверху)
3. В левом меню выберите **Deploy**
4. Нажмите **Run workflow** → **Run workflow**
5. Подождите — зелёная галочка = успех, красный крестик = ошибка

Если ошибка — нажмите на workflow run, чтобы увидеть логи и понять что пошло не так.

---

## 5. Как работает деплой

### Автоматический (при push в main)

Вот что происходит, когда вы пушите код в ветку `main`:

```
Вы делаете: git push origin main
         │
         ▼
GitHub Actions запускает 3 проверки параллельно:
  ├── Lint (Pint) — проверка стиля кода
  ├── Tests (Pest) — запуск тестов
  └── Build (Vite) — сборка фронтенда
         │
         ▼  (все 3 прошли)
GitHub Actions запускает деплой:
  1. Подключается к серверу по SSH
  2. Выполняет git pull (скачивает новый код)
  3. Собирает Docker-образы
  4. Запускает миграции
  5. Перезапускает контейнеры
  6. Ждёт health check (60 секунд)
         │
         ▼
Сайт обновлён!
```

### Ручной деплой (без GitHub Actions)

Если вы не настраивали GitHub Actions или хотите обновить вручную:

```bash
# Подключиться к серверу
ssh deploy@103.75.127.131

# Перейти в проект
cd /home/deploy/golden-connect

# Скачать новый код
git pull

# Обновить
./deploy/scripts/update.sh
```

### Откат (если что-то сломалось)

Если после обновления сайт сломался:

```bash
ssh deploy@103.75.127.131
cd /home/deploy/golden-connect
./deploy/scripts/rollback.sh
```

Это вернёт предыдущую рабочую версию.

---

## 6. SSL-сертификат (HTTPS)

**Зачем:** Без SSL сайт работает по `http://` — браузер показывает «Не защищено», а пароли пользователей передаются в открытом виде.

### Вариант A: Certbot (Let's Encrypt) — бесплатный сертификат

**Требуется:** доменное имя (например, `golden-connect.com`), направленное на ваш сервер.

#### Шаг 1 — Установить Certbot

```bash
ssh root@103.75.127.131
sudo apt install certbot -y
```

#### Шаг 2 — Остановить Nginx (чтобы освободить порт 80)

```bash
su - deploy
cd /home/deploy/golden-connect
docker compose -f compose.yml -f compose.production.yml stop nginx
```

#### Шаг 3 — Получить сертификат

```bash
# Замените ваш-домен.com на ваш реальный домен
sudo certbot certonly --standalone -d ваш-домен.com
```

Certbot спросит email — введите свой. Согласитесь с условиями. Если всё хорошо, увидите:

```
Successfully received certificate.
Certificate is saved at: /etc/letsencrypt/live/ваш-домен.com/fullchain.pem
Key is saved at:         /etc/letsencrypt/live/ваш-домен.com/privkey.pem
```

#### Шаг 4 — Подключить сертификат к Nginx

Отредактируйте файл `docker/nginx/conf.d/default.conf` — добавьте блок для HTTPS **перед** существующим блоком `server`:

```nginx
# Редирект HTTP → HTTPS
server {
    listen 80;
    server_name ваш-домен.com;
    return 301 https://$host$request_uri;
}

# HTTPS
server {
    listen 443 ssl;
    server_name ваш-домен.com;

    ssl_certificate /etc/letsencrypt/live/ваш-домен.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ваш-домен.com/privkey.pem;

    # ... всё остальное из текущего блока server (root, location и т.д.)
}
```

#### Шаг 5 — Подключить папку сертификатов к Docker

В `compose.yml` добавьте volume для nginx:

```yaml
nginx:
  volumes:
    - ./docker/nginx/conf.d:/etc/nginx/conf.d:ro
    - /etc/letsencrypt:/etc/letsencrypt:ro    # ← добавить эту строку
```

#### Шаг 6 — Запустить Nginx

```bash
docker compose -f compose.yml -f compose.production.yml up -d nginx
```

#### Шаг 7 — Обновить APP_URL в .env

```bash
nano .env
# Изменить:
# APP_URL=http://103.75.127.131
# На:
# APP_URL=https://ваш-домен.com
```

Перезапустить app чтобы обновить config cache:

```bash
docker compose -f compose.yml -f compose.production.yml restart app
```

> **Автопродление:** Certbot сам обновляет сертификаты через systemd timer. Проверить: `sudo certbot renew --dry-run`

### Вариант B: Cloudflare Proxy (проще, но нужен Cloudflare)

Если ваш домен подключён к Cloudflare:

1. Зайдите в Cloudflare Dashboard → выберите домен
2. **DNS** → A-запись: `@` → `103.75.127.131` → включите оранжевое облако (**Proxied**)
3. **SSL/TLS** → выберите режим **Full**
4. Готово — Cloudflare сам обеспечит HTTPS

Никаких изменений на сервере не нужно. Обновите `APP_URL` в `.env` на `https://ваш-домен.com`.

---

## 7. Мониторинг

### Health Check — автоматическая проверка что сайт жив

**Зачем:** Если сайт упадёт ночью — вы узнаете из лога, а не от пользователей.

#### Шаг 1 — Открыть crontab

На сервере под пользователем `deploy`:

```bash
crontab -e
```

Если спросит какой редактор — выберите `1` (nano).

#### Шаг 2 — Добавить строку в конец файла

```
*/5 * * * * /home/deploy/golden-connect/deploy/scripts/health-check.sh >> /home/deploy/health.log 2>&1
```

Это будет проверять здоровье сервисов каждые 5 минут и записывать результат в `/home/deploy/health.log`.

Сохранить: `Ctrl+O` → `Enter` → `Ctrl+X`

### Backup — автоматический бэкап базы данных

#### Шаг 1 — Открыть crontab (если ещё не открыт)

```bash
crontab -e
```

#### Шаг 2 — Добавить строку

```
0 3 * * * /home/deploy/golden-connect/deploy/scripts/backup.sh >> /home/deploy/backup.log 2>&1
```

Это будет делать бэкап базы данных каждый день в 3:00 ночи.

#### Шаг 3 — Проверить что cron задачи добавлены

```bash
crontab -l
```

Должно показать обе строки.

---

## Чеклист перед первым деплоем

Пройдитесь по списку и убедитесь что всё выполнено:

- [ ] Docker установлен на сервере (шаг 2.1)
- [ ] Пользователь `deploy` создан (шаг 2.2)
- [ ] SSH-ключ для GitHub Actions создан (шаг 2.3)
- [ ] Firewall настроен (шаг 2.4)
- [ ] Репозиторий склонирован на сервер (шаг 3.1)
- [ ] `.env` заполнен production-значениями (шаг 3.2)
- [ ] Первичный деплой выполнен — `./deploy/scripts/deploy.sh` (шаг 3.4)
- [ ] Сайт открывается в браузере по IP
- [ ] (Опционально) GitHub Actions настроен (шаг 4)
- [ ] (Опционально) SSL-сертификат настроен (шаг 6)
- [ ] (Опционально) Cron для health check и backup добавлен (шаг 7)

## See Also

- [Deployment](deployment.md) — Docker-архитектура и скрипты
- [Configuration](configuration.md) — Переменные окружения
