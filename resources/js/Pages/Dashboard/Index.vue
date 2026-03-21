<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();
const page = usePage();

const user = computed(() => page.props.auth?.user);

const userName = computed(() => {
    return user.value?.name || '';
});
</script>

<template>
    <Head :title="t('dashboard.welcome')" />

    <AppLayout>
        <div class="dashboard-welcome">
            <h1 class="dashboard-welcome__title">
                {{ t('dashboard.welcome') }}<template v-if="userName">, {{ userName }}</template>!
            </h1>
            <p class="dashboard-welcome__subtitle">
                {{ t('dashboard.subtitle') }}
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
.dashboard-welcome {
    margin-bottom: 1rem;
}

@media (min-width: 640px) {
    .dashboard-welcome {
        margin-bottom: 2rem;
    }
}

.dashboard-welcome__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.25rem;
}

@media (min-width: 640px) {
    .dashboard-welcome__title {
        font-size: 1.875rem;
        margin-bottom: 0.5rem;
    }
}

.dashboard-welcome__subtitle {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}

@media (min-width: 640px) {
    .dashboard-welcome__subtitle {
        font-size: 1rem;
    }
}
</style>
