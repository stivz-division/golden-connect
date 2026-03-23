import { onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { backButton, miniApp } from '@telegram-apps/sdk'

/**
 * Управляет нативным Telegram Back Button в зависимости от текущего URL.
 * - /dashboard → клик закрывает Mini App
 * - остальные страницы → клик возвращает назад (history.back)
 */
export function useTelegramBackButton() {
    if (!backButton.mount.isAvailable()) return

    backButton.mount()

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

    // Обновляем при каждой навигации Inertia
    updateBackButton(window.location.pathname)

    const removeListener = router.on('navigate', (event) => {
        updateBackButton(event.detail.page.url)
    })

    onUnmounted(() => {
        removeListener()
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
