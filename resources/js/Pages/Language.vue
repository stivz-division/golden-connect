<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations.js';
import logoNav from '@/../images/logo-nav.png';

const props = defineProps({
    locales: {
        type: Array,
        required: true,
    },
    currentLocale: {
        type: String,
        required: true,
    },
});

const { t } = useTranslations();
const selected = ref(props.currentLocale);
const processing = ref(false);
const switching = ref(false);

function selectLocale(code) {
    if (code === selected.value || switching.value) return;

    selected.value = code;
    switching.value = true;

    router.patch(route('locale.update'), { locale: code }, {
        preserveScroll: true,
        onFinish: () => {
            switching.value = false;
        },
    });
}

function submit() {
    processing.value = true;
    router.post(route('locale.store'), { locale: selected.value }, {
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head :title="t('language.title')" />

    <div class="auth-page">
        <!-- Decorative blurs -->
        <div class="auth-glow auth-glow--violet" />
        <div class="auth-glow auth-glow--gold" />

        <div class="lang-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <!-- Header -->
            <div class="auth-header">
                <div class="auth-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.467.732-3.558" />
                    </svg>
                </div>
                <h1 class="auth-title">
                    {{ t('language.title') }}
                </h1>
                <p class="auth-subtitle">
                    {{ t('language.subtitle') }}
                </p>
            </div>

            <!-- Language Grid -->
            <div class="lang-grid" role="radiogroup" :aria-label="t('language.title')">
                <button
                    v-for="locale in locales"
                    :key="locale.code"
                    type="button"
                    role="radio"
                    :aria-checked="selected === locale.code"
                    class="lang-option"
                    :class="{ selected: selected === locale.code }"
                    @click="selectLocale(locale.code)"
                >
                    <span class="lang-flag" aria-hidden="true">{{ locale.flag }}</span>
                    <span class="lang-name">
                        <span class="lang-name-native">{{ locale.name }}</span>
                        <span class="lang-name-english">{{ locale.name_en }}</span>
                    </span>
                    <span class="lang-check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                </button>
            </div>

            <!-- Continue Button -->
            <button
                type="button"
                class="auth-submit"
                :disabled="processing || switching"
                @click="submit"
            >
                {{ t('language.continue') }} &rarr;
            </button>
        </div>
    </div>
</template>

<style scoped>
/* === Auth Page Background === */
.auth-page {
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0D0B1A 0%, #1A1333 100%);
    padding: 1rem;
    position: relative;
    overflow: hidden;
}

.auth-glow {
    position: absolute;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    pointer-events: none;
}

.auth-glow--violet {
    top: -200px;
    right: -200px;
    background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, transparent 70%);
}

.auth-glow--gold {
    bottom: -200px;
    left: -200px;
    background: radial-gradient(circle, rgba(245, 197, 66, 0.08) 0%, transparent 70%);
}

/* === Card === */
.lang-card {
    width: 100%;
    max-width: 520px;
    background: rgba(45, 33, 80, 0.6);
    border: 1px solid rgba(168, 85, 247, 0.3);
    border-radius: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow:
        0 20px 60px rgba(0, 0, 0, 0.4),
        0 0 30px rgba(168, 85, 247, 0.3);
    padding: 2.5rem;
    position: relative;
    z-index: 1;
}

@media (max-width: 640px) {
    .lang-card {
        padding: 1.5rem 1.25rem;
        border-radius: 12px;
        max-width: 100%;
    }
}

/* === Logo === */
.auth-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.auth-logo img {
    height: 40px;
    width: auto;
    image-rendering: crisp-edges;
}

/* === Header === */
.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(168, 85, 247, 0.1));
    border: 1px solid rgba(168, 85, 247, 0.4);
    margin-bottom: 1rem;
    color: #A855F7;
}

.auth-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #FFFFFF;
    margin-bottom: 0.375rem;
}

.auth-subtitle {
    font-size: 0.875rem;
    color: #9CA3AF;
}

/* === Language Grid === */
.lang-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 640px) {
    .lang-grid {
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }
}

.lang-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(13, 11, 26, 0.6);
    border: 1px solid rgba(168, 85, 247, 0.15);
    border-radius: 12px;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s, transform 0.15s, box-shadow 0.2s;
    user-select: none;
    text-align: left;
}

.lang-option:hover {
    border-color: rgba(168, 85, 247, 0.4);
    background: rgba(45, 33, 80, 0.4);
    transform: translateY(-1px);
}

.lang-option:focus-visible {
    outline: 2px solid #A855F7;
    outline-offset: 2px;
}

.lang-option.selected {
    border-color: #A855F7;
    background: rgba(168, 85, 247, 0.15);
    box-shadow: 0 0 16px rgba(168, 85, 247, 0.2);
}

@media (max-width: 640px) {
    .lang-option {
        padding: 0.75rem;
        gap: 0.5rem;
    }
}

.lang-flag {
    font-size: 1.5rem;
    line-height: 1;
    flex-shrink: 0;
}

@media (max-width: 640px) {
    .lang-flag {
        font-size: 1.25rem;
    }
}

.lang-name {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 0;
}

.lang-name-native {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #FFFFFF;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lang-name-english {
    font-size: 0.75rem;
    color: #9CA3AF;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 640px) {
    .lang-name-native { font-size: 0.8125rem; }
    .lang-name-english { font-size: 0.6875rem; }
}

.lang-check {
    margin-left: auto;
    color: #A855F7;
    opacity: 0;
    transition: opacity 0.2s;
    flex-shrink: 0;
}

.lang-option.selected .lang-check {
    opacity: 1;
}

/* === Submit Button === */
.auth-submit {
    width: 100%;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #F5C542, #FFB800);
    color: #000000;
    font-family: inherit;
    font-weight: 700;
    font-size: 1rem;
    line-height: 1.5;
    letter-spacing: 0.02em;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
    box-shadow: 0 4px 20px rgba(245, 197, 66, 0.3);
}

.auth-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 28px rgba(245, 197, 66, 0.45);
    opacity: 0.93;
}

.auth-submit:active {
    transform: scale(0.98);
}

.auth-submit:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
    pointer-events: none;
}

@media (max-width: 640px) {
    .auth-submit {
        font-size: 0.875rem;
        padding: 0.75rem 1.25rem;
    }
}
</style>
