<script setup lang="ts">
import { Head, useForm, Link, usePage } from '@inertiajs/vue3'
import { Phone, Mail, UserCircle, Users, AlertTriangle } from 'lucide-vue-next'
import { useTranslations } from '@/Composables/useTranslations.js'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import logoNav from '@/../images/logo-nav.png'
import { ref as vueRef, computed, onMounted, onUnmounted } from 'vue'

const { t } = useTranslations()
const page = usePage()

const refUuid = (page.props.ref as string | null) ?? null
const mentorUuid = (page.props.mentorUuid as string | null) ?? null

const mentorInfo = vueRef<{ uuid: string; name: string; surname: string } | null>(null)
const mentorLoading = vueRef(false)
const mentorError = vueRef(false)

const activeTab = vueRef<'phone' | 'email'>('phone')
const codeSent = vueRef(false)
const countdown = vueRef(0)
let timer: ReturnType<typeof setInterval> | null = null

const form = useForm({
    type: 'phone' as string,
    identifier: '',
    code: '',
    ref: refUuid ?? '',
})

onMounted(async () => {
    if (!mentorUuid) return

    mentorLoading.value = true
    try {
        const response = await fetch(`/api/mentor/${encodeURIComponent(mentorUuid)}`)
        if (response.ok) {
            mentorInfo.value = await response.json()
        } else if (refUuid) {
            mentorError.value = true
        }
    } catch {
        if (refUuid) {
            mentorError.value = true
        }
    } finally {
        mentorLoading.value = false
    }
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

const sendCode = () => {
    form.post(route('register.send-code'), {
        preserveScroll: true,
        onSuccess: () => {
            codeSent.value = true
            startCountdown()
        },
    })
}

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('code'),
    })
}

const canSendCode = computed(() => {
    return form.identifier.length > 0 && !form.processing && countdown.value === 0
})

const canSubmit = computed(() => {
    if (mentorError.value || mentorLoading.value) return false
    return !form.processing && form.code.length === 6
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>

<template>
    <Head :title="t('auth.registerButton')" />

    <AuthLayout>
        <div class="auth-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <div class="auth-header">
                <div class="auth-icon">
                    <UserCircle :size="24" />
                </div>
                <h1 class="auth-title">{{ t('auth.welcome') }}</h1>
                <p class="auth-subtitle">{{ t('auth.otp.registerSubtitle') }}</p>
            </div>

            <!-- Mentor loading -->
            <div v-if="mentorUuid && mentorLoading" class="mentor-card mentor-card--loading">
                <div class="mentor-card__icon">
                    <Users :size="20" />
                </div>
                <div class="mentor-card__info">
                    <span class="mentor-card__label">{{ t('auth.yourMentor') }}</span>
                    <span class="mentor-card__loading">{{ t('auth.mentorLoading') }}</span>
                </div>
            </div>

            <!-- Mentor found -->
            <div v-if="mentorInfo && !mentorLoading" class="mentor-card">
                <div class="mentor-card__icon">
                    <Users :size="20" />
                </div>
                <div class="mentor-card__info">
                    <span class="mentor-card__label">{{ t('auth.yourMentor') }}</span>
                    <span class="mentor-card__name">{{ mentorInfo.name }} {{ mentorInfo.surname }}</span>
                </div>
            </div>

            <!-- Mentor not found -->
            <div v-if="refUuid && mentorError && !mentorLoading" class="mentor-error">
                <div class="mentor-error__icon">
                    <AlertTriangle :size="24" />
                </div>
                <h2 class="mentor-error__title">{{ t('auth.registrationUnavailable') }}</h2>
                <p class="mentor-error__message">{{ t('auth.mentorNotFoundMessage') }}</p>
            </div>

            <template v-if="!mentorError">
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

                    <input type="hidden" name="ref" :value="form.ref" />

                    <button
                        v-if="codeSent"
                        type="submit"
                        class="auth-submit"
                        :disabled="!canSubmit"
                    >
                        {{ t('auth.registerButton') }}
                    </button>
                </form>
            </template>

            <div v-if="!mentorError" class="auth-footer">
                <p class="auth-footer-text">
                    {{ t('auth.haveAccount') }}
                    <Link class="auth-switch-btn" :href="route('login')">
                        {{ t('auth.loginButton') }}
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
