import { router } from '@inertiajs/vue3'
import { backButton, miniApp } from '@telegram-apps/sdk'

let currentHandler: VoidFunction | null = null

function updateBackButton(currentUrl: string) {
    if (currentHandler && backButton.offClick.isAvailable()) {
        backButton.offClick(currentHandler)
        currentHandler = null
    }

    const path = currentUrl.split('?')[0]
    const isDashboard = path === '/dashboard' || path === '/dashboard/'

    if (isDashboard) {
        currentHandler = () => {
            if (miniApp.close.isAvailable()) {
                miniApp.close()
            }
        }
    } else {
        currentHandler = () => {
            window.history.back()
        }
    }

    if (backButton.show.isAvailable()) {
        backButton.show()
    }

    if (currentHandler && backButton.onClick.isAvailable()) {
        backButton.onClick(currentHandler)
    }
}

/**
 * Инициализирует глобальное управление Telegram Back Button.
 * - /dashboard → клик закрывает Mini App
 * - остальные страницы → клик возвращает назад (history.back)
 *
 * Вызывать один раз при старте приложения.
 */
export function initTelegramBackButton() {
    if (!backButton.mount.isAvailable()) return

    backButton.mount()

    updateBackButton(window.location.pathname)

    router.on('navigate', (event) => {
        updateBackButton(event.detail.page.url)
    })
}
