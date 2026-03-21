# Golden Connect

## Language
Отвечай на русском языке.

## Git
Коммит пиши на русском.

## Laravel Pint
Нужно проверять ./vendor/bin/pint --test

## Exception
Для новых ошибок создаем свои Exception

## Form Request
Не забывай про валидацию если она нужна

## Docker
Работа идет в контейнере: docker exec -it app sh

## Inertia
При разработке нужно не забывать о корретном информировании клиента на наличие ошибок или чем-то другом
Учитывать состояние processing, loading защищать от двойных кликов

## Локализация
Проект поддерживает два языка русский и английский.
Нужно не забывать поддерживать локализацию

## Telegram Mini App (Safe Area)
При работе с frontend учитывать safe area insets для корректного отображения в fullscreen режиме Telegram Mini Apps.
Использовать CSS-переменные: `--tg-viewport-safe-area-inset-*` и `--tg-viewport-content-safe-area-inset-*` с fallback `0px`.
Утилитарный класс `.tg-safe-area` в `app.css`, в `auth.css` уже учтено.

## Настраиваемые переменные
- nova_get_setting

Например: Появляется ссылка на чат с тех. поддеркой. Эта ссылка может измениться в любой момент.
Нужно получать ссылку на чат с тех поддержкой через функцию nova_get_setting('example_link', 'default'), и добавить в
NovaServiceProvider
в NovaSettings Text::make('Тест переменной', 'example_link'),
