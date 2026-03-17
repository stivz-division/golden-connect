<script setup>
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-900 flex items-center justify-center">
        <h1 class="text-4xl font-bold text-white">Dashboard</h1>
    </div>
</template>
