<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Gift, Users, ChevronRight } from 'lucide-vue-next';
import heroBanner from '@/../images/hero-banner.jpg';

const { t } = useTranslations();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const userName = computed(() => user.value?.name || '');
const referralCount = computed(() => page.props.referralCount ?? 0);
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

        <!-- Hero Banner -->
        <div class="dashboard-banner">
            <div class="dashboard-banner__container">
                <img
                    :src="heroBanner"
                    alt="Golden Connect"
                    class="dashboard-banner__image"
                />
                <div class="dashboard-banner__overlay" />
                <div class="dashboard-banner__glow" />
            </div>
        </div>

        <!-- Referral Banner -->
        <Link :href="route('invite')" class="dashboard-referral">
            <div class="dashboard-referral__icon">
                <Gift :size="20" />
            </div>
            <div class="dashboard-referral__content">
                <h3 class="dashboard-referral__title">{{ t('dashboard.referral.title') }}</h3>
                <p class="dashboard-referral__subtitle">{{ t('dashboard.referral.subtitle') }}</p>
            </div>
            <div class="dashboard-referral__actions">
                <div class="dashboard-referral__badge">
                    <Users :size="14" />
                    <span>{{ referralCount }}</span>
                </div>
                <ChevronRight :size="20" class="dashboard-referral__chevron" />
            </div>
        </Link>
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

/* Hero Banner */
.dashboard-banner {
    margin-bottom: 1.5rem;
}

@media (min-width: 640px) {
    .dashboard-banner {
        margin-bottom: 2rem;
    }
}

.dashboard-banner__container {
    position: relative;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    transition: transform 0.5s, box-shadow 0.5s;
    cursor: pointer;
}

@media (min-width: 640px) {
    .dashboard-banner__container {
        border-radius: 1rem;
    }
}

.dashboard-banner__container:hover {
    transform: scale(1.01);
    box-shadow: 0 20px 60px rgba(168, 85, 247, 0.3);
}

.dashboard-banner__image {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.dashboard-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.3), transparent, transparent);
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.dashboard-banner__container:hover .dashboard-banner__overlay {
    opacity: 1;
}

.dashboard-banner__glow {
    position: absolute;
    inset: -4px;
    background: linear-gradient(to right, #9333ea, #ec4899, #9333ea);
    border-radius: 0.75rem;
    opacity: 0;
    filter: blur(16px);
    transition: opacity 0.5s;
    pointer-events: none;
    z-index: -1;
}

@media (min-width: 640px) {
    .dashboard-banner__glow {
        border-radius: 1rem;
    }
}

.dashboard-banner__container:hover .dashboard-banner__glow {
    opacity: 0.2;
}

/* Referral Banner */
.dashboard-referral {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 1rem;
    background: rgba(245, 197, 66, 0.08);
    border: 1px solid rgba(245, 197, 66, 0.3);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    cursor: pointer;
}

@media (min-width: 640px) {
    .dashboard-referral {
        gap: 1rem;
        padding: 1.25rem;
        margin-bottom: 2rem;
    }
}

.dashboard-referral:hover {
    transform: scale(1.01);
    box-shadow: 0 8px 28px rgba(245, 197, 66, 0.15);
}

.dashboard-referral:focus-visible {
    outline: 2px solid #F5C542;
    outline-offset: 2px;
}

.dashboard-referral__icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: linear-gradient(135deg, #F5C542, #FFB800);
    color: #000;
}

@media (min-width: 640px) {
    .dashboard-referral__icon {
        width: 2.75rem;
        height: 2.75rem;
    }
}

.dashboard-referral__content {
    flex: 1;
    min-width: 0;
}

.dashboard-referral__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #fff;
}

@media (min-width: 640px) {
    .dashboard-referral__title {
        font-size: 1rem;
    }
}

.dashboard-referral__subtitle {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
}

.dashboard-referral__actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

@media (min-width: 640px) {
    .dashboard-referral__actions {
        gap: 0.75rem;
    }
}

.dashboard-referral__badge {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.5rem;
    background: rgba(168, 85, 247, 0.15);
    border: 1px solid rgba(168, 85, 247, 0.25);
    color: #A855F7;
}

.dashboard-referral__badge span {
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
}

.dashboard-referral__chevron {
    color: rgba(255, 255, 255, 0.5);
}
</style>
