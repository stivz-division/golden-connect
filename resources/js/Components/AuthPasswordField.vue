<script setup lang="ts">
import { shallowRef } from 'vue'
import { Lock, Eye, EyeOff } from 'lucide-vue-next'
import { useTranslations } from '@/Composables/useTranslations.js'

const { t } = useTranslations()

const props = defineProps<{
    id: string
    label: string
    placeholder: string
    error?: string
    autocomplete?: string
}>()

const model = defineModel<string>({ required: true })

const showPassword = shallowRef(false)
</script>

<template>
    <div class="auth-field">
        <label class="auth-label" :for="props.id">{{ props.label }}</label>
        <div class="auth-input-wrapper">
            <span class="auth-input-icon">
                <Lock :size="16" />
            </span>
            <input
                :id="props.id"
                v-model="model"
                :type="showPassword ? 'text' : 'password'"
                class="auth-input has-icon has-toggle"
                :class="{ error: props.error }"
                :placeholder="props.placeholder"
                :autocomplete="props.autocomplete ?? 'new-password'"
            />
            <button
                type="button"
                class="auth-toggle-btn"
                :aria-label="showPassword ? t('auth.hidePassword') : t('auth.showPassword')"
                @click="showPassword = !showPassword"
            >
                <EyeOff v-if="showPassword" :size="16" />
                <Eye v-else :size="16" />
            </button>
        </div>
        <p v-if="props.error" class="auth-error">
            {{ props.error }}
        </p>
    </div>
</template>
