import { createInertiaApp, router } from '@inertiajs/vue3';
import { createApp, defineComponent, h, ref } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import { useCart } from '@/composables/useCart';
import { useFavorites } from '@/composables/useFavorites';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const cart = useCart();
const favorites = useFavorites();

const isLoading = ref(false);
const progress = ref(0);
let progressInterval: ReturnType<typeof setInterval> | undefined;

router.on('start', () => {
    cart.close();
    favorites.close();
    isLoading.value = true;
    progress.value = 0;
    progressInterval = setInterval(() => {
        progress.value = Math.min(progress.value + Math.random() * 20, 85);
    }, 200);
});

router.on('finish', () => {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = undefined;
    }

    cart.close();
    favorites.close();
    progress.value = 100;
    setTimeout(() => {
        isLoading.value = false;
        progress.value = 0;
    }, 300);
});

const ProgressBar = defineComponent({
    setup() {
        return () =>
            isLoading.value
                ? h('div', {
                      class: 'fixed top-0 left-0 z-[9999] h-1 bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-500 transition-all duration-300 ease-out shadow-lg shadow-cyan-500/50',
                      style: { width: `${progress.value}%` },
                  })
                : null;
    },
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
                return () => h('div', { class: 'min-h-screen' }, [h(ProgressBar), h(App, props)]);
            },
        };

        const app = createApp({ render: () => h(Root) });

        app.use(plugin);
        app.use(ZiggyVue);

        app.mount(el as HTMLElement);
    },
});

initializeTheme();
