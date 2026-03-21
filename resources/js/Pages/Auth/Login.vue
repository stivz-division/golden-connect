<script setup lang="ts">
import { Head, useForm, Link, usePage } from '@inertiajs/vue3'
import { Phone, Mail, User } from 'lucide-vue-next'
import { useTranslations } from '@/Composables/useTranslations.js'
import { useRecaptcha } from '@/Composables/useRecaptcha'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import logoNav from '@/../images/logo-nav.png'
import { ref, computed, onUnmounted } from 'vue'

const { t } = useTranslations()
const page = usePage()
const { execute: executeRecaptcha } = useRecaptcha()

const activeTab = ref<'phone' | 'email'>('phone')
const codeSent = ref(false)
const countdown = ref(0)
const sendingCode = ref(false)
let timer: ReturnType<typeof setInterval> | null = null

const form = useForm({
    type: 'phone' as string,
    identifier: '',
    code: '',
})

const switchTab = (tab: 'phone' | 'email') => {
    activeTab.value = tab
    form.type = tab
    form.identifier = ''
    form.code = ''
    form.clearErrors()
    codeSent.value = false
}

const startCountdown = () => {
    countdown.value = 60
    timer = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0 && timer) {
            clearInterval(timer)
            timer = null
        }
    }, 1000)
}

const sendCode = async () => {
    sendingCode.value = true

    let recaptchaToken: string | null = null
    try {
        recaptchaToken = await executeRecaptcha('send_code')
    } catch {
        // Отправляем без токена — бэкенд вернёт ошибку валидации
    }

    form.transform((data) => ({
        ...data,
        ...(recaptchaToken ? { recaptcha_token: recaptchaToken } : {}),
    })).post(route('login.send-code'), {
        preserveScroll: true,
        onSuccess: () => {
            codeSent.value = true
            startCountdown()
        },
        onFinish: () => {
            sendingCode.value = false
        },
    })
}

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('code'),
    })
}

const canSendCode = computed(() => {
    return form.identifier.length > 0 && !form.processing && !sendingCode.value && countdown.value === 0
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>

<template>
    <Head :title="t('auth.loginButton')" />

    <AuthLayout>
        <div class="auth-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <div class="auth-header">
                <div class="auth-icon">
                    <User :size="24" />
                </div>
                <h1 class="auth-title">{{ t('auth.welcomeBack') }}</h1>
                <p class="auth-subtitle">{{ t('auth.otp.loginSubtitle') }}</p>
            </div>

            <div class="auth-tabs">
                <button
                    type="button"
                    class="auth-tab"
                    :class="{ 'auth-tab--active': activeTab === 'phone' }"
                    @click="switchTab('phone')"
                >
                    <Phone :size="16" />
                    {{ t('auth.otp.phone') }}
                </button>
                <button
                    type="button"
                    class="auth-tab"
                    :class="{ 'auth-tab--active': activeTab === 'email' }"
                    @click="switchTab('email')"
                >
                    <Mail :size="16" />
                    {{ t('auth.otp.email') }}
                </button>
            </div>

            <form class="auth-form" @submit.prevent="submit">
                <div class="auth-field">
                    <label class="auth-label" for="identifier">
                        {{ activeTab === 'phone' ? t('auth.otp.phoneLabel') : t('auth.otp.emailLabel') }}
                    </label>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <component :is="activeTab === 'phone' ? Phone : Mail" :size="16" />
                        </span>
                        <input
                            id="identifier"
                            v-model="form.identifier"
                            :type="activeTab === 'phone' ? 'tel' : 'email'"
                            class="auth-input has-icon"
                            :class="{ error: form.errors.identifier }"
                            :placeholder="activeTab === 'phone' ? t('auth.otp.phonePlaceholder') : t('auth.otp.emailPlaceholder')"
                        />
                    </div>
                    <p v-if="form.errors.identifier" class="auth-error">
                        {{ form.errors.identifier }}
                    </p>
                </div>

                <p v-if="form.errors.recaptcha_token" class="auth-error">
                    {{ form.errors.recaptcha_token }}
                </p>

                <div class="auth-field auth-code-row">
                    <button
                        type="button"
                        class="auth-send-code"
                        :disabled="!canSendCode"
                        @click="sendCode"
                    >
                        {{ countdown > 0 ? t('auth.otp.resendIn', { seconds: countdown }) : t('auth.otp.sendCode') }}
                    </button>
                </div>

                <div v-if="codeSent" class="auth-field">
                    <label class="auth-label" for="code">{{ t('auth.otp.codeLabel') }}</label>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        class="auth-input auth-input--code"
                        :class="{ error: form.errors.code }"
                        :placeholder="t('auth.otp.codePlaceholder')"
                        autocomplete="one-time-code"
                    />
                    <p v-if="form.errors.code" class="auth-error">
                        {{ form.errors.code }}
                    </p>
                </div>

                <button
                    v-if="codeSent"
                    type="submit"
                    class="auth-submit"
                    :disabled="form.processing || form.code.length !== 6"
                >
                    {{ t('auth.loginButton') }}
                </button>
            </form>

            <div class="auth-footer">
                <p class="auth-footer-text">
                    {{ t('auth.noAccount') }}
                    <Link class="auth-switch-btn" :href="route('register')">
                        {{ t('auth.registerButton') }}
                    </Link>
                </p>
            </div>
        </div>

        <div class="auth-info">
            <p>{{ t('info.guaranteedProfit') }}</p>
            <p>{{ t('info.lotRange') }}</p>
        </div>
    </AuthLayout>
</template>
