<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Globe, ChevronDown } from 'lucide-vue-next';

const page = usePage();
const isOpen = ref(false);
const dropdownRef = ref(null);

const currentLocale = () => {
    const locales = page.props.locales || [];
    return locales.find(l => l.code === page.props.locale) || locales[0];
};

const switchLocale = (code) => {
    if (code === page.props.locale) {
        isOpen.value = false;
        return;
    }

    router.patch(route('locale.update'), { locale: code }, {
        preserveScroll: true,
        onFinish: () => {
            isOpen.value = false;
        },
    });
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="lang-switcher">
        <button
            class="lang-switcher__trigger"
            aria-label="Select language"
            @click="isOpen = !isOpen"
        >
            <Globe :size="16" class="lang-switcher__icon" />
            <span class="lang-switcher__current">
                <span class="lang-switcher__flag">{{ currentLocale()?.flag }}</span>
                <span class="lang-switcher__code">{{ currentLocale()?.short }}</span>
            </span>
            <ChevronDown
                :size="14"
                class="lang-switcher__chevron"
                :class="{ 'lang-switcher__chevron--open': isOpen }"
            />
        </button>

        <div v-if="isOpen" class="lang-switcher__dropdown">
            <button
                v-for="locale in page.props.locales"
                :key="locale.code"
                class="lang-switcher__option"
                :class="{ 'lang-switcher__option--active': page.props.locale === locale.code }"
                @click="switchLocale(locale.code)"
            >
                <span class="lang-switcher__option-flag">{{ locale.flag }}</span>
                <span class="lang-switcher__option-name">{{ locale.name }}</span>
                <span
                    v-if="page.props.locale === locale.code"
                    class="lang-switcher__option-dot"
                />
            </button>
        </div>
    </div>
</template>

<style scoped>
.lang-switcher {
    position: relative;
}

.lang-switcher__trigger {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 0.5rem;
    padding: 0.375rem 0.625rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    color: #fff;
    transition: background 0.2s;
    min-height: 34px;
}

.lang-switcher__trigger:hover {
    background: rgba(255, 255, 255, 0.2);
}

.lang-switcher__icon {
    color: rgba(255, 255, 255, 0.7);
    flex-shrink: 0;
}

.lang-switcher__current {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.lang-switcher__flag {
    font-size: 0.875rem;
}

.lang-switcher__code {
    font-size: 0.75rem;
    font-weight: 500;
}

.lang-switcher__chevron {
    color: rgba(255, 255, 255, 0.7);
    flex-shrink: 0;
    transition: transform 0.2s;
}

.lang-switcher__chevron--open {
    transform: rotate(180deg);
}

.lang-switcher__dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    width: 10rem;
    border-radius: 0.75rem;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    background: rgba(45, 33, 80, 0.95);
    border: 1px solid rgba(168, 85, 247, 0.3);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    z-index: 50;
}

.lang-switcher__option {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 0.875rem;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8125rem;
    transition: background 0.15s;
}

.lang-switcher__option:hover {
    background: rgba(255, 255, 255, 0.05);
}

.lang-switcher__option--active {
    background: rgba(168, 85, 247, 0.2);
    color: #A855F7;
    font-weight: 500;
}

.lang-switcher__option--active:hover {
    background: rgba(168, 85, 247, 0.2);
}

.lang-switcher__option-flag {
    font-size: 1.125rem;
}

.lang-switcher__option-name {
    flex: 1;
}

.lang-switcher__option-dot {
    width: 0.375rem;
    height: 0.375rem;
    border-radius: 50%;
    background: #A855F7;
    flex-shrink: 0;
}
</style>