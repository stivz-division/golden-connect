import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { useTelegramApp } from '@/Composables/useTelegramApp';
import { initTelegramBackButton } from '@/Composables/useTelegramBackButton';

// Инициализируем Telegram SDK если приложение запущено внутри Telegram
const { initialize, setupFullscreen } = useTelegramApp();
initialize();

// Монтируем viewport и привязываем CSS-переменные safe area при каждом старте
setupFullscreen();

// Инициализируем Back Button для навигации в Telegram Mini App
initTelegramBackButton();

const appName = import.meta.env.VITE_APP_NAME || 'Golden Connect';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        app.config.globalProperties.$t = function (key, replacements = {}) {
            const translations = this.$page.props.translations || {};
            let value = translations[key] ?? key;

            Object.entries(replacements).forEach(([placeholder, replacement]) => {
                value = value.replace(new RegExp(`:${placeholder}`, 'g'), replacement);
            });

            return value;
        };

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});