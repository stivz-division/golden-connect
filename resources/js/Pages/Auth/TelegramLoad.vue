<script setup lang="ts">
import {Head, router} from '@inertiajs/vue3'
import {useTranslations} from '@/Composables/useTranslations.js'
import {useTelegram} from '@/Composables/useTelegram'
import {useTelegramApp} from '@/Composables/useTelegramApp'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import {AlertTriangle, Loader2, RefreshCw} from 'lucide-vue-next'
import {onMounted, ref} from 'vue'
import logoNav from '@/../images/logo-nav.png'

const { t } = useTranslations()
const { getRawInitData, buildAuthUrl } = useTelegram()
const { setupFullscreen } = useTelegramApp()

const loading = ref(true)
const error = ref<string | null>(null)

function authenticate() {
    loading.value = true
    error.value = null

    const rawInitData = getRawInitData()

    if (!rawInitData) {
        loading.value = false
        error.value = t('telegram.open_in_telegram') !== 'telegram.open_in_telegram'
            ? t('telegram.open_in_telegram')
            : 'Please open this page from Telegram'
        return
    }

    const authUrl = buildAuthUrl(rawInitData)

    router.get(authUrl, {}, {
        onError: () => {
            loading.value = false
            error.value = t('telegram.auth_error')
        },
    })
}

onMounted(() => {
    setupFullscreen()
    authenticate()
})
</script>

<template>
    <Head :title="t('telegram.loading')" />

    <AuthLayout :hide-language-switcher="true">
        <div class="auth-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <!-- Loading state -->
            <div v-if="loading" class="telegram-loading">
                <Loader2 :size="48" class="telegram-loading__spinner" />
                <p class="telegram-loading__text">{{ t('telegram.loading') }}</p>
            </div>

            <!-- Error state -->
            <div v-if="error && !loading" class="telegram-error">
                <div class="telegram-error__icon">
                    <AlertTriangle :size="48" />
                </div>
                <p class="telegram-error__text">{{ error }}</p>
                <button
                    type="button"
                    class="auth-submit telegram-error__retry"
                    @click="authenticate"
                >
                    <RefreshCw :size="16" />
                    {{ t('telegram.retry') }}
                </button>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.telegram-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 40px 0;
}

.telegram-loading__spinner {
    animation: spin 1s linear infinite;
    color: var(--color-primary, #d4a843);
}

.telegram-loading__text {
    color: var(--color-text-secondary, #9ca3af);
    font-size: 14px;
}

.telegram-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 40px 0;
}

.telegram-error__icon {
    color: var(--color-danger, #ef4444);
}

.telegram-error__text {
    color: var(--color-text-secondary, #9ca3af);
    font-size: 14px;
    text-align: center;
}

.telegram-error__retry {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
