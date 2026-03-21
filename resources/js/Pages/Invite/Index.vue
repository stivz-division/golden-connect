<script setup>
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import {
    ArrowLeft,
    Gift,
    ExternalLink,
    UserPlus,
    Users,
    TrendingUp,
    Copy,
    Check,
    QrCode,
    Send,
    Info,
    ChevronRight,
} from 'lucide-vue-next';

const { t } = useTranslations();
const page = usePage();

const referralLink = computed(() => page.props.referralLink);
const telegramLink = computed(() => page.props.telegramLink);
const referralCode = computed(() => page.props.referralCode);
const stats = computed(() => page.props.stats);

const copiedWeb = ref(false);
const copiedTg = ref(false);
const showQRWeb = ref(false);
const showQRTg = ref(false);

const copyToClipboard = (text) => {
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).catch(() => {});
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
};

const handleCopyWeb = () => {
    copyToClipboard(referralLink.value);
    copiedWeb.value = true;
    setTimeout(() => { copiedWeb.value = false; }, 2000);
};

const handleCopyTg = () => {
    copyToClipboard(telegramLink.value);
    copiedTg.value = true;
    setTimeout(() => { copiedTg.value = false; }, 2000);
};

const goBack = () => {
    window.history.back();
};

const statItems = computed(() => [
    { label: t('invite.clicks'), value: stats.value.totalClicks, icon: ExternalLink, color: '#60A5FA' },
    { label: t('invite.registrations'), value: stats.value.registrations, icon: UserPlus, color: '#22C55E' },
    { label: t('invite.activeReferrals'), value: stats.value.activeReferrals, icon: Users, color: '#A855F7' },
    { label: t('invite.earned'), value: `$${stats.value.totalEarned}`, icon: TrendingUp, color: '#F5C542' },
]);

const steps = [
    { num: 1, title: t('invite.step1Title'), desc: t('invite.step1Desc'), icon: UserPlus, color: '#22C55E' },
    { num: 2, title: t('invite.step2Title'), desc: t('invite.step2Desc'), icon: TrendingUp, color: '#60A5FA' },
    { num: 3, title: t('invite.step3Title'), desc: t('invite.step3Desc'), icon: Gift, color: '#F5C542' },
];

const levels = [
    { level: 1, desc: t('invite.level1Desc') },
    { level: 2, desc: t('invite.level2Desc') },
    { level: 3, desc: t('invite.level3Desc') },
    { level: 4, desc: t('invite.level4Desc') },
    { level: 5, desc: t('invite.level5Desc') },
];
</script>

<template>
    <Head :title="t('invite.title')" />

    <AppLayout>
        <!-- Back Button -->
        <button class="invite-back-button" @click="goBack">
            <ArrowLeft :size="16" />
            {{ t('common.back') }}
        </button>

        <!-- Page Title -->
        <div class="invite-title-container">
            <div class="invite-title-icon">
                <Gift :size="20" />
            </div>
            <div>
                <h1 class="invite-title">{{ t('invite.title') }}</h1>
                <p class="invite-subtitle">{{ t('invite.subtitle') }}</p>
            </div>
        </div>

        <!-- Referral Stats -->
        <div class="invite-stats-grid">
            <div
                v-for="(stat, i) in statItems"
                :key="i"
                class="invite-stat-card"
            >
                <component :is="stat.icon" :size="20" class="invite-stat-icon" :style="{ color: stat.color }" />
                <p class="invite-stat-value">{{ stat.value }}</p>
                <p class="invite-stat-label">{{ stat.label }}</p>
            </div>
        </div>

        <!-- Referral Link Card -->
        <div class="invite-link-card">
            <h2 class="invite-card-title">{{ t('invite.yourLink') }}</h2>
            <p class="invite-card-description">{{ t('invite.linkDescription') }}</p>

            <!-- Web Referral Link -->
            <div class="invite-link-section invite-link-section--bordered">
                <div class="invite-link-label-row">
                    <p class="invite-link-label">{{ t('dashboard.referral.webLink') }}</p>
                    <span class="invite-link-badge invite-link-badge--web">
                        <ExternalLink :size="12" />
                        Web
                    </span>
                </div>
                <div class="invite-link-input-group">
                    <input
                        type="text"
                        :value="referralLink"
                        readonly
                        class="invite-link-input"
                        @click="$event.target.select()"
                    />
                    <button class="invite-copy-button" @click="handleCopyWeb">
                        <Check v-if="copiedWeb" :size="16" />
                        <Copy v-else :size="16" />
                        <span>{{ copiedWeb ? t('invite.copied') : t('invite.copy') }}</span>
                    </button>
                </div>
                <button
                    class="invite-qr-toggle invite-qr-toggle--web"
                    @click="showQRWeb = !showQRWeb"
                >
                    <QrCode :size="16" />
                    <span>{{ showQRWeb ? t('invite.hideQR') : t('invite.showQR') }}</span>
                </button>
                <div v-if="showQRWeb" class="invite-qr-container">
                    <img
                        :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(referralLink)}`"
                        alt="QR Code Web"
                        class="invite-qr-image"
                    />
                    <p class="invite-qr-text">{{ t('invite.scanQR') }}</p>
                </div>
            </div>

            <!-- Telegram Mini App Link -->
            <div v-if="telegramLink" class="invite-link-section">
                <div class="invite-link-label-row">
                    <p class="invite-link-label">{{ t('dashboard.referral.tgLink') }}</p>
                    <span class="invite-link-badge invite-link-badge--tg">
                        <Send :size="12" />
                        Telegram
                    </span>
                </div>
                <div class="invite-link-input-group">
                    <input
                        type="text"
                        :value="telegramLink"
                        readonly
                        class="invite-link-input"
                        @click="$event.target.select()"
                    />
                    <button class="invite-copy-button" @click="handleCopyTg">
                        <Check v-if="copiedTg" :size="16" />
                        <Copy v-else :size="16" />
                        <span>{{ copiedTg ? t('invite.copied') : t('invite.copy') }}</span>
                    </button>
                </div>
                <button
                    class="invite-qr-toggle invite-qr-toggle--tg"
                    @click="showQRTg = !showQRTg"
                >
                    <QrCode :size="16" />
                    <span>{{ showQRTg ? t('invite.hideQR') : t('invite.showQR') }}</span>
                </button>
                <div v-if="showQRTg" class="invite-qr-container">
                    <img
                        :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(telegramLink)}`"
                        alt="QR Code Telegram"
                        class="invite-qr-image"
                    />
                    <p class="invite-qr-text">{{ t('invite.scanQR') }}</p>
                </div>
            </div>

            <!-- Referral Code -->
            <p class="invite-code-label">
                {{ t('invite.codeLabel') }}: <span class="invite-code-value">{{ referralCode }}</span>
            </p>
        </div>

        <!-- How Referral Program Works -->
        <div class="invite-how-works-card">
            <h2 class="invite-card-title">{{ t('invite.howItWorks') }}</h2>
            <p class="invite-how-works-desc">{{ t('invite.howItWorksDesc') }}</p>

            <!-- Steps -->
            <div class="invite-steps">
                <div v-for="step in steps" :key="step.num" class="invite-step">
                    <div
                        class="invite-step-icon"
                        :style="{ background: `${step.color}20`, color: step.color }"
                    >
                        <component :is="step.icon" :size="20" />
                    </div>
                    <div class="invite-step-content">
                        <p class="invite-step-title">{{ step.num }}. {{ step.title }}</p>
                        <p class="invite-step-desc">{{ step.desc }}</p>
                    </div>
                </div>
            </div>

            <!-- 5 Levels -->
            <h3 class="invite-levels-title">{{ t('invite.level') }} 1–5</h3>
            <div class="invite-levels-container">
                <div v-for="item in levels" :key="item.level" class="invite-level-item">
                    <div class="invite-level-badge">{{ item.level }}</div>
                    <div class="invite-level-content">
                        <p class="invite-level-title">{{ t('invite.level') }} {{ item.level }}</p>
                        <p class="invite-level-description">{{ item.desc }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="invite-note">
                <Info :size="16" class="invite-note-icon" />
                <p class="invite-note-text">{{ t('invite.referralNote') }}</p>
            </div>
            <div class="invite-note invite-note--subtle">
                <Info :size="16" class="invite-note-icon" />
                <p class="invite-note-text">{{ t('invite.separateProgram') }}</p>
            </div>
        </div>

        <!-- CTA to view team (stub) -->
        <button class="invite-team-button" disabled>
            <div class="invite-team-button__content">
                <Users :size="20" />
                <span>{{ t('invite.viewTeam') }}</span>
            </div>
            <ChevronRight :size="20" class="invite-team-button__chevron" />
        </button>
    </AppLayout>
</template>

<style>
@import '@/../css/invite.css';
</style>
