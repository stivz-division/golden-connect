<script setup>
import AppHeader from '@/Components/AppHeader.vue';
import { useTelegramBackButton } from '@/Composables/useTelegramBackButton';

useTelegramBackButton();
</script>

<template>
    <div class="app-layout">
        <AppHeader />

        <main class="app-layout__main">
            <slot />
        </main>
    </div>
</template>

<style scoped>
.app-layout {
    min-height: 100vh;
    width: 100%;
    background: linear-gradient(135deg, #0D0B1A 0%, #1A1333 100%);
}

.app-layout__main {
    max-width: 80rem;
    margin: 0 auto;
    padding: 1rem 0.75rem 2rem;
    padding-bottom: calc(2rem + var(--tg-viewport-safe-area-inset-bottom, 0px));
}

@media (min-width: 640px) {
    .app-layout__main {
        padding: 2rem 1.5rem 2rem;
        padding-bottom: calc(2rem + var(--tg-viewport-safe-area-inset-bottom, 0px));
    }
}

@media (min-width: 1024px) {
    .app-layout__main {
        padding: 2rem 2rem 2rem;
        padding-bottom: calc(2rem + var(--tg-viewport-safe-area-inset-bottom, 0px));
    }
}
</style>
