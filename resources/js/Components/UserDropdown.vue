<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { ChevronDown, LogOut } from 'lucide-vue-next';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();
const page = usePage();

const isOpen = ref(false);
const dropdownRef = ref(null);

const user = computed(() => page.props.auth?.user);

const userInitial = computed(() => {
    if (user.value?.name) {
        return user.value.name.charAt(0).toUpperCase();
    }
    return '?';
});

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

const logout = () => {
    router.post(route('logout'), {}, {
        preserveScroll: true,
    });
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="user-dropdown">
        <button
            class="user-dropdown__trigger"
            @click="isOpen = !isOpen"
        >
            <span class="user-dropdown__avatar">
                {{ userInitial }}
            </span>
            <ChevronDown
                :size="16"
                class="user-dropdown__chevron"
                :class="{ 'user-dropdown__chevron--open': isOpen }"
            />
        </button>

        <Transition name="dropdown">
            <div v-if="isOpen" class="user-dropdown__menu">
                <button
                    class="user-dropdown__item user-dropdown__item--danger"
                    @click="logout"
                >
                    <LogOut :size="20" />
                    <span>{{ t('nav.logout') }}</span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.user-dropdown {
    position: relative;
}

.user-dropdown__trigger {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.625rem;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
}

.user-dropdown__trigger:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
}

.user-dropdown__avatar {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #A855F7, #7C3AED);
}

.user-dropdown__chevron {
    color: rgba(255, 255, 255, 0.7);
    transition: transform 0.2s;
}

.user-dropdown__chevron--open {
    transform: rotate(180deg);
}

.user-dropdown__menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    width: 12rem;
    border-radius: 0.75rem;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    background: rgba(45, 33, 80, 0.95);
    border: 1px solid rgba(168, 85, 247, 0.3);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    z-index: 50;
}

.user-dropdown__item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.15s;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-dropdown__item:last-child {
    border-bottom: none;
}

.user-dropdown__item:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.user-dropdown__item--danger:hover {
    color: #f87171;
    background: rgba(239, 68, 68, 0.1);
}

.user-dropdown__item-icon--purple {
    color: #A855F7;
}

.user-dropdown__item-icon--gold {
    color: #F5C542;
}

/* Transition */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
