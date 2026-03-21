import { ref } from 'vue'
import {
    init,
    miniApp,
    viewport,
    swipeBehavior,
    closingBehavior,
    themeParams,
} from '@telegram-apps/sdk'
import { postEvent, retrieveRawInitData } from '@telegram-apps/bridge'

const initialized = ref(false)
let cleanup: VoidFunction | null = null

function isTelegramEnv(): boolean {
    try {
        return !!retrieveRawInitData()
    } catch {
        return false
    }
}

export function useTelegramApp() {
    /**
     * Инициализирует SDK и монтирует необходимые компоненты.
     * Вызывать один раз при старте приложения.
     */
    function initialize(): boolean {
        if (initialized.value) return true

        if (!isTelegramEnv()) return false

        try {
            cleanup = init()
            initialized.value = true
            return true
        } catch {
            return false
        }
    }

    /**
     * Монтирует miniApp, viewport и вспомогательные компоненты,
     * затем переводит приложение в fullscreen.
     * Безопасно вызывать вне Telegram — ничего не произойдёт.
     */
    async function setupFullscreen() {
        if (!initialize()) return

        // 1. Монтируем miniApp
        if (miniApp.mountSync.isAvailable()) {
            miniApp.mountSync()
        }

        // 2. Монтируем и привязываем тему (автоматически реагирует на themeChanged)
        if (themeParams.mountSync.isAvailable()) {
            themeParams.mountSync()
        }
        if (themeParams.bindCssVars.isAvailable()) {
            themeParams.bindCssVars()
        }

        // 3. Монтируем viewport
        if (viewport.mount.isAvailable()) {
            await viewport.mount()
        }

        // 4. Расширяем на максимальную высоту
        if (viewport.expand.isAvailable()) {
            viewport.expand()
        }

        // 5. Запрашиваем fullscreen (Bot API 8.0+)
        if (viewport.requestFullscreen.isAvailable()) {
            try {
                await viewport.requestFullscreen()
            } catch {
                // Fullscreen не поддерживается или уже активен — игнорируем
            }
        }

        // 6. Отключаем вертикальные свайпы чтобы пользователь случайно не закрыл приложение
        if (swipeBehavior.mount.isAvailable()) {
            swipeBehavior.mount()
        }
        if (swipeBehavior.disableVertical.isAvailable()) {
            swipeBehavior.disableVertical()
        }

        // 7. Включаем подтверждение закрытия
        if (closingBehavior.mount.isAvailable()) {
            closingBehavior.mount()
        }
        if (closingBehavior.enableConfirmation.isAvailable()) {
            closingBehavior.enableConfirmation()
        }

        // 8. Устанавливаем header color для fullscreen (прозрачный по умолчанию)
        if (miniApp.setHeaderColor.isAvailable()) {
            miniApp.setHeaderColor('bg_color')
        }

        // 9. Привязываем CSS-переменные miniApp и viewport (включая safe area insets)
        if (miniApp.bindCssVars.isAvailable()) {
            miniApp.bindCssVars()
        }
        if (viewport.bindCssVars.isAvailable()) {
            viewport.bindCssVars()
        }

        // 10. Блокируем ориентацию в portrait (Bot API 8.0+)
        try {
            postEvent('web_app_toggle_orientation_lock', { locked: true })
        } catch {
            // Не поддерживается в текущей версии клиента — игнорируем
        }

        // 11. Сообщаем Telegram что приложение готово к отображению
        if (miniApp.ready.isAvailable()) {
            miniApp.ready()
        }
    }

    function destroy() {
        if (cleanup) {
            cleanup()
            cleanup = null
            initialized.value = false
        }
    }

    return {
        initialized,
        initialize,
        setupFullscreen,
        destroy,
        isFullscreen: viewport.isFullscreen,
    }
}
