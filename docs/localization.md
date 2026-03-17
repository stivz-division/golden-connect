
# Локализация (Locale System)

Система выбора и управления языком интерфейса. Поддерживаются русский и английский.

## Архитектура

```
config/locales.php                    ← Единый источник правды
        │
        ├──► SetLocale middleware      ← Определение языка + redirect
        │         │
        │         ▼
        ├──► HandleInertiaRequests     ← Передача locale/translations на фронт
        │
        ├──► LocaleController          ← GET /language, PATCH /locale, POST /locale
        │
        └──► Language.vue              ← Страница выбора языка
```

## Конфигурация

Единый источник доступных языков — `config/locales.php`:

```php
return [
    'default' => 'ru',
    'fallback' => 'en',
    'available' => [
        'ru' => ['name' => 'Русский', 'name_en' => 'Russian', 'flag' => '🇷🇺', 'short' => 'RU'],
        'en' => ['name' => 'English', 'name_en' => 'English', 'flag' => '🇬🇧', 'short' => 'EN'],
    ],
];
```

Для добавления нового языка — добавить запись в `available` и создать файл `lang/{code}.json`.

## Маршруты

| Метод | URL | Имя | Описание |
|-------|-----|------|----------|
| GET | `/language` | `locale.index` | Страница выбора языка |
| PATCH | `/locale` | `locale.update` | Переключение языка (остаётся на странице) |
| POST | `/locale` | `locale.store` | Подтверждение языка (redirect на `/`) |

## Middleware: SetLocale

Зарегистрирован в `bootstrap/app.php` перед `HandleInertiaRequests`.

Приоритет определения locale:

1. `user.language` (авторизованный пользователь) — *TODO: пока заглушка*
2. `session('locale')` — если есть и валидный
3. Redirect на `/language` — если язык не определён

Исключения (не редиректятся): маршруты `locale.index`, `locale.store`, `locale.update`.

## Inertia Shared Data

`HandleInertiaRequests` передаёт на фронтенд:

| Prop | Тип | Описание |
|------|-----|----------|
| `locale` | `string` | Текущий код языка (`ru`, `en`) |
| `locales` | `array` | Список доступных языков с name, flag, short |
| `translations` | `object` | Переводы из `lang/{locale}.json` |

## Фронтенд

### Composable `useTranslations`

```js
import { useTranslations } from '@/Composables/useTranslations.js';

const { t } = useTranslations();
t('language.title');                  // → "Выберите язык"
t('greeting', { name: 'John' });      // → подстановка :name
```

Если ключ не найден — возвращает сам ключ как fallback.

### Глобальный `$t`

Зарегистрирован в `app.js` через `app.config.globalProperties.$t`. Доступен в любом template без импорта:

```vue
<template>
    <h1>{{ $t('language.title') }}</h1>
</template>
```

### Страница Language.vue

- Двухэтапный UX: клик по карточке языка → PATCH переключает язык и перезагружает страницу с новыми переводами → кнопка «Продолжить» подтверждает выбор
- Тёмная тема с glassmorphism (violet/gold)
- Grid карточек с флагами, нативным и английским названием
- `role="radiogroup"` + `aria-checked` для accessibility

## Файлы переводов

Переводы хранятся в `lang/{locale}.json` в плоском формате с точечной нотацией:

```json
{
    "language.title": "Выберите язык",
    "language.subtitle": "Выберите предпочитаемый язык интерфейса",
    "language.continue": "Продолжить",
    "common.back": "Назад"
}
```

При добавлении новых строк — добавлять ключ в **оба** файла (`ru.json` и `en.json`).

## Добавление нового языка

1. Добавить запись в `config/locales.php` → `available`
2. Создать `lang/{code}.json` с переводами всех ключей
3. Готово — язык появится на странице выбора автоматически

## Тесты

```bash
./vendor/bin/pest tests/Feature/LocaleTest.php
```

Покрыто:
- Гость без locale → redirect на `/language`
- Гость с locale → нет redirect
- POST валидный → session обновлена, redirect на `/`
- POST невалидный → ошибка валидации
- PATCH валидный → session обновлена, redirect на `/language`
- PATCH невалидный → ошибка валидации
- GET `/language` → нет redirect loop
- Структура `config('locales.available')` корректна

## See Also

- [Configuration](configuration.md) — переменные окружения и настройки
- [Architecture](architecture.md) — структура проекта и DDD
- [Getting Started](getting-started.md) — установка и первый запуск
