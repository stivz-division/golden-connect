<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { UserCircle, User, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { useTranslations } from '@/Composables/useTranslations.js';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import logoNav from '@/../images/logo-nav.png';

const { t } = useTranslations();

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    login: '',
    email: '',
    password: '',
    password_confirmation: '',
    name: '',
    surname: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
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
                            autocomplete="new-password"
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

                <div class="auth-field">
                    <label class="auth-label" for="password_confirmation">{{ t('auth.confirmPassword') }}</label>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <Lock :size="16" />
                        </span>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="auth-input has-icon has-toggle"
                            :class="{ error: form.errors.password_confirmation }"
                            :placeholder="t('placeholder.repeatPassword')"
                            autocomplete="new-password"
                        />
                        <button
                            type="button"
                            class="auth-toggle-btn"
                            :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                            @click="showConfirmPassword = !showConfirmPassword"
                        >
                            <EyeOff v-if="showConfirmPassword" :size="16" />
                            <Eye v-else :size="16" />
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="auth-error">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

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

                <button
                    type="submit"
                    class="auth-submit"
                    :disabled="form.processing"
                >
                    {{ t('auth.registerButton') }}
                </button>
            </form>

            <div class="auth-footer">
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
