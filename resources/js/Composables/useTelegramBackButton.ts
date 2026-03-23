import { watch, onUnmounted, type Ref } from 'vue'
import { backButton, miniApp } from '@telegram-apps/sdk'

/**
 * Управляет нативным Telegram Back Button в зависимости от текущего URL.
 * - /dashboard → клик закрывает Mini App
 * - остальные страницы → клик возвращает назад (history.back)
 */
export function useTelegramBackButton(url: Ref<string> | (() => string)) {
    if (!backButton.mount.isAvailable()) return

    backButton.mount()

    let currentHandler: VoidFunction | null = null

    function updateBackButton(currentUrl: string) {
        // Убираем предыдущий обработчик
        if (currentHandler && backButton.offClick.isAvailable()) {
            backButton.offClick(currentHandler)
            currentHandler = null
        }

        const path = currentUrl.split('?')[0]
        const isDashboard = path === '/dashboard' || path === '/dashboard/'

        if (isDashboard) {
            // На dashboard — кнопка закрывает приложение
            currentHandler = () => {
                if (miniApp.close.isAvailable()) {
                    miniApp.close()
                }
            }
        } else {
            // На остальных страницах — назад
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

    watch(url, (newUrl) => updateBackButton(newUrl), { immediate: true })

    onUnmounted(() => {
        if (currentHandler && backButton.offClick.isAvailable()) {
            backButton.offClick(currentHandler)
            currentHandler = null
        }
        if (backButton.hide.isAvailable()) {
            backButton.hide()
        }
        backButton.unmount()
    })
}
