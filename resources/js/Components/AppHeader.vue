<script setup>
import { Link } from '@inertiajs/vue3';
import logoNav from '@/../images/logo-nav.png';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import UserDropdown from '@/Components/UserDropdown.vue';
</script>

<template>
    <header class="app-header">
        <div class="app-header__container">
            <Link :href="route('dashboard')" class="app-header__logo-link">
                <img
                    :src="logoNav"
                    alt="Golden Connect"
                    class="app-header__logo"
                />
            </Link>

            <div class="app-header__actions">
                <LanguageSwitcher />
                <UserDropdown />
            </div>
        </div>
    </header>
</template>

<style scoped>
.app-header {
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    background: rgba(13, 11, 26, 0.95);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: calc(var(--tg-viewport-content-safe-area-inset-top, 0px) + var(--tg-viewport-safe-area-inset-top, 0px));
}

.app-header__container {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 3.5rem;
}

@media (min-width: 640px) {
    .app-header__container {
        padding: 0 1.5rem;
    }
}

@media (min-width: 1024px) {
    .app-header__container {
        padding: 0 2rem;
    }
}

.app-header__logo-link {
    display: flex;
    align-items: center;
}

.app-header__logo {
    height: 2.5rem;
    width: auto;
    image-rendering: crisp-edges;
}

.app-header__actions {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}
</style>
