import { usePage } from '@inertiajs/vue3'

declare global {
    interface Window {
        grecaptcha: {
            ready: (cb: () => void) => void
            execute: (siteKey: string, options: { action: string }) => Promise<string>
        }
    }
}

let scriptLoaded = false
let scriptLoading: Promise<void> | null = null

function loadScript(siteKey: string): Promise<void> {
    if (scriptLoaded) return Promise.resolve()

    if (scriptLoading) return scriptLoading

    scriptLoading = new Promise<void>((resolve, reject) => {
        const script = document.createElement('script')
        script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`
        script.async = true

        script.onload = () => {
            scriptLoaded = true
            resolve()
        }

        script.onerror = () => {
            scriptLoading = null
            reject(new Error('Failed to load reCAPTCHA script'))
        }

        document.head.appendChild(script)
    })

    return scriptLoading
}

export function useRecaptcha() {
    const page = usePage()
    const siteKey = (page.props.recaptchaSiteKey as string) || ''

    function isEnabled(): boolean {
        return !!siteKey
    }

    function isTelegramWebView(): boolean {
        return !!(window as any).Telegram?.WebApp?.initData
    }

    async function execute(action: string): Promise<string | null> {
        if (!isEnabled() || isTelegramWebView()) {
            return null
        }

        await loadScript(siteKey)

        return new Promise<string>((resolve, reject) => {
            window.grecaptcha.ready(() => {
                window.grecaptcha
                    .execute(siteKey, { action })
                    .then(resolve)
                    .catch(reject)
            })
        })
    }

    return {
        isEnabled,
        execute,
    }
}
