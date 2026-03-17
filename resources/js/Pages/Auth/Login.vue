<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { User, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { useTranslations } from '@/Composables/useTranslations.js';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import logoNav from '@/../images/logo-nav.png';

const { t } = useTranslations();

const showPassword = ref(false);

const form = useForm({
    login: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
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
                <p class="auth-subtitle">{{ t('auth.loginSubtitle') }}</p>
            </div>

            <form class="auth-form" @submit.prevent="submit">
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
                    <label class="auth-label" for="password">{{ t('auth.password') }}</label>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <Lock :size="16" />
                        </span>
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="auth-input has-icon has-toggle"
                            :class="{ error: form.errors.password }"
                            :placeholder="t('placeholder.enterPassword')"
                            autocomplete="current-password"
                        />
                        <button
                            type="button"
                            class="auth-toggle-btn"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            @click="showPassword = !showPassword"
                        >
                            <EyeOff v-if="showPassword" :size="16" />
                            <Eye v-else :size="16" />
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="auth-error">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="auth-forgot">
                    <Link class="auth-forgot-link" :href="route('password.request')">
                        {{ t('auth.forgotPassword') }}
                    </Link>
                </div>

                <button
                    type="submit"
                    class="auth-submit"
                    :disabled="form.processing"
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