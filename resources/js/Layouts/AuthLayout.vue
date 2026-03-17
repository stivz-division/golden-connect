<script setup>
import '../../css/auth.css';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

defineProps({
    wide: {
        type: Boolean,
        default: false,
    },
    hideLanguageSwitcher: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="auth-page">
        <div v-if="!hideLanguageSwitcher" class="auth-lang-switcher">
            <LanguageSwitcher />
        </div>

        <div
            class="auth-container"
            :class="{ 'auth-container--wide': wide }"
        >
            <slot />
        </div>
    </div>
</template>
