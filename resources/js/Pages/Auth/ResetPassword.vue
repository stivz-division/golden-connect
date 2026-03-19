<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { Lock, Mail } from 'lucide-vue-next'
import { useTranslations } from '@/Composables/useTranslations.js'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import AuthPasswordField from '@/Components/AuthPasswordField.vue'
import logoNav from '@/../images/logo-nav.png'

const { t } = useTranslations()

const props = defineProps<{
    token: string
    email: string
}>()

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head :title="t('reset.title')" />

    <AuthLayout>
        <div class="auth-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <div class="auth-header">
                <div class="auth-icon">
                    <Lock :size="24" />
                </div>
                <h1 class="auth-title">{{ t('reset.title') }}</h1>
                <p class="auth-subtitle">{{ t('reset.subtitle') }}</p>
            </div>

            <form class="auth-form" @submit.prevent="submit">
                <div class="auth-field">
                    <label class="auth-label" for="email">
                        {{ t('auth.email') }}
                    </label>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <Mail :size="16" />
                        </span>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="auth-input has-icon"
                            readonly
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
                    :label="t('reset.newPassword')"
                    :placeholder="t('placeholder.enterPassword')"
                    :error="form.errors.password"
                    autocomplete="new-password"
                />

                <AuthPasswordField
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :label="t('reset.confirmPassword')"
                    :placeholder="t('placeholder.repeatPassword')"
                    :error="form.errors.password_confirmation"
                    autocomplete="new-password"
                />

                <button
                    type="submit"
                    class="auth-submit"
                    :disabled="form.processing"
                >
                    {{ t('reset.resetButton') }}
                </button>
            </form>

            <div class="auth-footer">
                <Link class="auth-switch-btn" :href="route('login')">
                    ← {{ t('auth.backToLogin') }}
                </Link>
            </div>
        </div>
    </AuthLayout>
</template>
