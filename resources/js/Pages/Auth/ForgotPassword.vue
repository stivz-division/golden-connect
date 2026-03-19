<script setup lang="ts">
import { shallowRef } from 'vue'
import { Head, useForm, Link, usePage } from '@inertiajs/vue3'
import { Mail } from 'lucide-vue-next'
import { useTranslations } from '@/Composables/useTranslations.js'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import logoNav from '@/../images/logo-nav.png'

const { t } = useTranslations()
const page = usePage()

const submittedEmail = shallowRef('')

const form = useForm({
    email: '',
})

const isSuccess = shallowRef(false)

const submit = () => {
    form.post(route('password.email'), {
        preserveState: true,
        onSuccess: () => {
            if (page.props.status) {
                submittedEmail.value = form.email
                isSuccess.value = true
            }
        },
    })
}
</script>

<template>
    <Head :title="t('forgot.title')" />

    <AuthLayout>
        <div class="auth-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <template v-if="isSuccess">
                <div class="auth-header">
                    <div class="auth-success-icon">✉️</div>
                    <h2 class="auth-title">{{ t('forgot.checkEmail') }}</h2>
                    <p class="auth-subtitle">{{ submittedEmail }}</p>
                </div>

                <div class="auth-info-box">
                    {{ t('forgot.instructionsSent') }}
                </div>

                <div class="auth-footer">
                    <Link :href="route('login')" class="auth-back-btn">
                        ← {{ t('auth.backToLogin') }}
                    </Link>
                </div>
            </template>

            <template v-else>
                <div class="auth-header">
                    <div class="auth-icon">
                        <Mail :size="24" />
                    </div>
                    <h1 class="auth-title">{{ t('forgot.title') }}</h1>
                    <p class="auth-subtitle">{{ t('forgot.subtitle') }}</p>
                </div>

                <form class="auth-form" @submit.prevent="submit">
                    <div class="auth-field">
                        <label class="auth-label" for="email">
                            {{ t('forgot.emailLabel') }}
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
                                :class="{ error: form.errors.email }"
                                :placeholder="t('placeholder.enterEmail')"
                                autocomplete="email"
                            />
                        </div>
                        <p v-if="form.errors.email" class="auth-error">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="auth-info-box">
                        {{ t('forgot.infoMessage') }}
                    </div>

                    <button
                        type="submit"
                        class="auth-submit"
                        :disabled="form.processing"
                    >
                        {{ t('forgot.sendInstructions') }}
                    </button>
                </form>

                <div class="auth-footer">
                    <Link class="auth-switch-btn" :href="route('login')">
                        ← {{ t('auth.backToLogin') }}
                    </Link>
                </div>
            </template>
        </div>
    </AuthLayout>
</template>
