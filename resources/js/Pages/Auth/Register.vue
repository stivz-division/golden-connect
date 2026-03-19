<script setup lang="ts">
import { Head, useForm, Link, usePage } from '@inertiajs/vue3'
import { UserCircle, User, Mail, Users, AlertTriangle } from 'lucide-vue-next'
import { useTranslations } from '@/Composables/useTranslations.js'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import AuthPasswordField from '@/Components/AuthPasswordField.vue'
import logoNav from '@/../images/logo-nav.png'
import { ref as vueRef, onMounted } from 'vue'

const { t } = useTranslations()
const page = usePage()

const refLogin = (page.props.ref as string | null) ?? null
const mentorLogin = (page.props.mentorLogin as string | null) ?? null

const mentorInfo = vueRef<{ name: string; surname: string; login: string } | null>(null)
const mentorLoading = vueRef(false)
const mentorError = vueRef(false)

const form = useForm({
    login: '',
    email: '',
    password: '',
    password_confirmation: '',
    name: '',
    surname: '',
    ref: refLogin ?? '',
})

onMounted(async () => {
    if (!mentorLogin) return

    mentorLoading.value = true
    try {
        const response = await fetch(`/api/mentor/${encodeURIComponent(mentorLogin)}`)
        if (response.ok) {
            mentorInfo.value = await response.json()
        } else if (refLogin) {
            mentorError.value = true
        }
    } catch {
        if (refLogin) {
            mentorError.value = true
        }
    } finally {
        mentorLoading.value = false
    }
})

const canSubmit = (): boolean => {
    if (mentorError.value || mentorLoading.value) return false
    return !form.processing
}

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head :title="t('auth.registerButton')" />

    <AuthLayout wide>
        <div class="auth-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <div class="auth-header">
                <div class="auth-icon">
                    <UserCircle :size="24" />
                </div>
                <h1 class="auth-title">{{ t('auth.welcome') }}</h1>
                <p class="auth-subtitle">{{ t('auth.registerSubtitle') }}</p>
            </div>

            <!-- Mentor loading -->
            <div v-if="mentorLogin && mentorLoading" class="mentor-card mentor-card--loading">
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
                    <span class="mentor-card__login">@{{ mentorInfo.login }}</span>
                </div>
            </div>

            <!-- Mentor not found — block registration (only when ref was explicitly provided) -->
            <div v-if="refLogin && mentorError && !mentorLoading" class="mentor-error">
                <div class="mentor-error__icon">
                    <AlertTriangle :size="24" />
                </div>
                <h2 class="mentor-error__title">{{ t('auth.registrationUnavailable') }}</h2>
                <p class="mentor-error__message">{{ t('auth.mentorNotFoundMessage') }}</p>
            </div>

            <form v-if="!mentorError" class="auth-form" @submit.prevent="submit">
                <div class="auth-field">
                    <label class="auth-label" for="login">{{ t('auth.login') }}</label>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <User :size="16" />
                        </span>
                        <input
                            id="login"
                            v-model="form.login"
                            type="text"
                            class="auth-input has-icon"
                            :class="{ error: form.errors.login }"
                            :placeholder="t('placeholder.enterLogin')"
                            autocomplete="username"
                        />
                    </div>
                    <p v-if="form.errors.login" class="auth-error">
                        {{ form.errors.login }}
                    </p>
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="email">{{ t('auth.email') }}</label>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <Mail :size="16" />
                        </span>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="auth-input has-icon"
                            :class="{ error: form.errors.email }"
                            :placeholder="t('placeholder.enterEmail')"
                            autocomplete="email"
                        />
                    </div>
                    <p v-if="form.errors.email" class="auth-error">
                        {{ form.errors.email }}
                    </p>
                </div>

                <AuthPasswordField
                    id="password"
                    v-model="form.password"
                    :label="t('auth.password')"
                    :placeholder="t('placeholder.enterPassword')"
                    :error="form.errors.password"
                />

                <AuthPasswordField
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :label="t('auth.confirmPassword')"
                    :placeholder="t('placeholder.repeatPassword')"
                    :error="form.errors.password_confirmation"
                />

                <div class="auth-name-row">
                    <div class="auth-field">
                        <label class="auth-label" for="name">{{ t('auth.firstName') }}</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="auth-input"
                            :class="{ error: form.errors.name }"
                            :placeholder="t('placeholder.enterFirstName')"
                            autocomplete="given-name"
                        />
                        <p v-if="form.errors.name" class="auth-error">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="surname">{{ t('auth.lastName') }}</label>
                        <input
                            id="surname"
                            v-model="form.surname"
                            type="text"
                            class="auth-input"
                            :class="{ error: form.errors.surname }"
                            :placeholder="t('placeholder.enterLastName')"
                            autocomplete="family-name"
                        />
                        <p v-if="form.errors.surname" class="auth-error">
                            {{ form.errors.surname }}
                        </p>
                    </div>
                </div>

                <input type="hidden" name="ref" :value="form.ref" />

                <button
                    type="submit"
                    class="auth-submit"
                    :disabled="!canSubmit()"
                >
                    {{ t('auth.registerButton') }}
                </button>
            </form>

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
