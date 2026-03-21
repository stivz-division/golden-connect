import { retrieveRawInitData } from '@telegram-apps/bridge'

export function useTelegram() {
    function getRawInitData(): string | null {
        try {
            const raw = retrieveRawInitData()
            return raw ?? null
        } catch {
            return null
        }
    }

    function buildAuthUrl(initDataRaw: string): string {
        return `/telegram/auth?${initDataRaw}`
    }

    return {
        getRawInitData,
        buildAuthUrl,
    }
}
