<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import {
  ArrowDown,
  Check,
  ChevronRight,
  CircleDollarSign,
  Copy,
  Download,
  ExternalLink,
  Gift,
  Info,
  Percent,
  QrCode,
  Send,
  Share2,
  Split,
  TrendingUp,
  UserPlus,
  Users,
  Wallet,
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

const downloadQR = async (url, filename) => {
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
    const response = await fetch(qrUrl);
    const blob = await response.blob();
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
};

const statItems = computed(() => [
    { label: t('invite.clicks'), value: stats.value.totalClicks, icon: ExternalLink, color: '#60A5FA' },
    { label: t('invite.registrations'), value: stats.value.registrations, icon: UserPlus, color: '#22C55E' },
    { label: t('invite.activeReferrals'), value: stats.value.activeReferrals, icon: Users, color: '#A855F7' },
    { label: t('invite.earned'), value: `$${stats.value.totalEarned}`, icon: TrendingUp, color: '#F5C542' },
]);

const levels = [
    { level: 1, label: t('invite.level1Label'), desc: t('invite.level1Desc') },
    { level: 2, label: t('invite.level2Label'), desc: t('invite.level2Desc') },
    { level: 3, label: t('invite.level3Label'), desc: t('invite.level3Desc') },
    { level: 4, label: t('invite.level4Label'), desc: t('invite.level4Desc') },
    { level: 5, label: t('invite.level5Label'), desc: t('invite.level5Desc') },
];
</script>

<template>
    <Head :title="t('invite.title')" />

    <AppLayout>
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
                    <button class="invite-qr-download" @click="downloadQR(referralLink, 'referral-qr-web.png')">
                        <Download :size="14" />
                        <span>{{ t('invite.downloadQR') }}</span>
                    </button>
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
                    <button class="invite-qr-download" @click="downloadQR(telegramLink, 'referral-qr-telegram.png')">
                        <Download :size="14" />
                        <span>{{ t('invite.downloadQR') }}</span>
                    </button>
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

            <!-- Visual Flow: Circle → Referral portion → 5-way split -->
            <div class="invite-flow">
                <div class="invite-flow-step">
                    <div class="invite-flow-step__icon invite-flow-step__icon--blue">
                        <CircleDollarSign :size="20" />
                    </div>
                    <div class="invite-flow-step__content">
                        <p class="invite-flow-step__title">{{ t('invite.flowCircleTitle') }}</p>
                        <p class="invite-flow-step__desc">{{ t('invite.flowCircleDesc') }}</p>
                    </div>
                </div>

                <div class="invite-flow-arrow">
                    <ArrowDown :size="16" />
                </div>

                <div class="invite-flow-step">
                    <div class="invite-flow-step__icon invite-flow-step__icon--purple">
                        <Split :size="20" />
                    </div>
                    <div class="invite-flow-step__content">
                        <p class="invite-flow-step__title">{{ t('invite.flowSplitTitle') }}</p>
                        <p class="invite-flow-step__desc">{{ t('invite.flowSplitDesc') }}</p>
                    </div>
                </div>

                <div class="invite-flow-arrow">
                    <ArrowDown :size="16" />
                </div>

                <div class="invite-flow-step">
                    <div class="invite-flow-step__icon invite-flow-step__icon--gold">
                        <Wallet :size="20" />
                    </div>
                    <div class="invite-flow-step__content">
                        <p class="invite-flow-step__title">{{ t('invite.flowEarnTitle') }}</p>
                        <p class="invite-flow-step__desc">{{ t('invite.flowEarnDesc') }}</p>
                    </div>
                </div>
            </div>

            <!-- Example Calculation -->
            <div class="invite-example">
                <div class="invite-example__header">
                    <Info :size="14" />
                    <span>{{ t('invite.exampleTitle') }}</span>
                </div>
                <p class="invite-example__text" v-html="t('invite.exampleText')"></p>
            </div>

            <!-- 5 Levels — Equal Split Visual -->
            <h3 class="invite-levels-title">
                <Share2 :size="16" />
                {{ t('invite.levelsTitle') }}
            </h3>
            <p class="invite-levels-subtitle">{{ t('invite.levelsSubtitle') }}</p>

            <!-- Equal split bar -->
            <div class="invite-split-bar">
                <div
                    v-for="item in levels"
                    :key="item.level"
                    class="invite-split-bar__segment"
                >
                    <span class="invite-split-bar__label">{{ item.level }}</span>
                </div>
            </div>
            <p class="invite-split-bar__caption">{{ t('invite.splitCaption') }}</p>

            <!-- Level Cards -->
            <div class="invite-levels-container">
                <div v-for="item in levels" :key="item.level" class="invite-level-item">
                    <div class="invite-level-badge">{{ item.level }}</div>
                    <div class="invite-level-content">
                        <p class="invite-level-title">{{ item.label }}</p>
                        <p class="invite-level-description">{{ item.desc }}</p>
                    </div>
                    <div class="invite-level-share">20%</div>
                </div>
            </div>

            <!-- Technical Lot Deduction -->
            <div class="invite-deduction-section">
                <h3 class="invite-deduction-title">
                    <div class="invite-deduction-title__icon">
                        <Percent :size="16" />
                    </div>
                    {{ t('invite.deductionTitle') }}
                </h3>
                <p class="invite-deduction-desc">{{ t('invite.deductionDesc') }}</p>

                <div class="invite-deduction-info">
                    <div class="invite-deduction-info__header">
                        <Info :size="14" />
                        <span>{{ t('invite.deductionHowTitle') }}</span>
                    </div>
                    <ul class="invite-deduction-info__list">
                        <li>{{ t('invite.deductionStep1') }}</li>
                        <li>{{ t('invite.deductionStep2') }}</li>
                        <li>{{ t('invite.deductionStep3') }}</li>
                    </ul>
                </div>

                <div class="invite-deduction-note">
                    <Info :size="14" class="invite-deduction-note__icon" />
                    <p class="invite-deduction-note__text">{{ t('invite.deductionNote') }}</p>
                </div>
            </div>

            <!-- Where money goes -->
            <div class="invite-note">
                <Info :size="16" class="invite-note-icon" />
                <p class="invite-note-text">{{ t('invite.referralNote') }}</p>
            </div>

            <!-- No active lot warning -->
            <div class="invite-note invite-note--subtle">
                <Info :size="16" class="invite-note-icon" />
                <p class="invite-note-text">{{ t('invite.noLotNote') }}</p>
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
