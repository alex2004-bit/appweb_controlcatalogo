import { createInertiaApp, router } from '@inertiajs/vue3';
import { createApp, h, ref } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm.js';
import { toast } from 'vue-sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const isLoading = ref(false);
const progress = ref(0);

router.on('start', () => {
    isLoading.value = true;
    progress.value = 0;
    const interval = setInterval(() => {
        progress.value = Math.min(progress.value + Math.random() * 20, 85);
    }, 200);
    router.on('finish', () => {
        clearInterval(interval);
        progress.value = 100;
        setTimeout(() => {
            isLoading.value = false;
            progress.value = 0;
        }, 300);
    }, { once: true });
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('public/'):
                return PublicLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
            case name.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: false,
    setup({ el, App, props, plugin }) {
        const Root = {
            setup() {
                return () => [
                    isLoading.value ? h('div', {
                        class: 'fixed top-0 left-0 z-[9999] h-1 bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-500 transition-all duration-300 ease-out shadow-lg shadow-cyan-500/50',
                        style: { width: `${progress.value}%` },
                    }) : null,
                    h(App, props),
                ];
            },
        };

        const app = createApp({ render: () => h(Root) });

        app.use(plugin);
        app.use(ZiggyVue);

        app.mount(el);
    },
});

initializeTheme();
