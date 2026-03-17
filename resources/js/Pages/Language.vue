<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Globe, Check } from 'lucide-vue-next';
import { useTranslations } from '@/Composables/useTranslations.js';
import AuthLayout from '@/Layouts/AuthLayout.vue';
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

    <AuthLayout wide hide-language-switcher>
        <div class="auth-card lang-card">
            <div class="auth-logo">
                <img :src="logoNav" alt="Golden Connect" />
            </div>

            <div class="auth-header">
                <div class="auth-icon">
                    <Globe :size="28" />
                </div>
                <h1 class="auth-title">
                    {{ t('language.title') }}
                </h1>
                <p class="auth-subtitle">
                    {{ t('language.subtitle') }}
                </p>
            </div>

            <div class="lang-grid" role="radiogroup" :aria-label="t('language.title')">
                <button
                    v-for="locale in locales"
                    :key="locale.code"
                    type="button"
                    role="radio"
                    :aria-checked="selected === locale.code"
                    class="lang-option"
                    :class="{ 'lang-option--selected': selected === locale.code }"
                    @click="selectLocale(locale.code)"
                >
                    <span class="lang-flag" aria-hidden="true">{{ locale.flag }}</span>
                    <span class="lang-name">
                        <span class="lang-name-native">{{ locale.name }}</span>
                        <span class="lang-name-english">{{ locale.name_en }}</span>
                    </span>
                    <span class="lang-check" aria-hidden="true">
                        <Check :size="18" :stroke-width="3" />
                    </span>
                </button>
            </div>

            <button
                type="button"
                class="auth-submit"
                :disabled="processing || switching"
                @click="submit"
            >
                {{ t('language.continue') }} &rarr;
            </button>
        </div>
    </AuthLayout>
</template>

<style scoped>
/* Language card — wider than default auth-card */
.lang-card {
    max-width: 520px;
}

/* Language Grid */
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
    color: inherit;
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

.lang-option--selected {
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

.lang-option--selected .lang-check {
    opacity: 1;
}
</style>
