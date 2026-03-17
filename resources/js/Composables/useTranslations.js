import { usePage } from '@inertiajs/vue3';

export function useTranslations() {
    const page = usePage();

    function t(key, replacements = {}) {
        const translations = page.props.translations || {};
        let value = translations[key] ?? key;

        Object.entries(replacements).forEach(([placeholder, replacement]) => {
            value = value.replace(new RegExp(`:${placeholder}`, 'g'), replacement);
        });

        return value;
    }

    return { t };
}
