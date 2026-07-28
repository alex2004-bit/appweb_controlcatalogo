import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) return;

        const type = data.type as 'success' | 'error' | 'info' | 'warning';
        if (['success', 'error', 'info', 'warning'].includes(type)) {
            toast[type](data.message, {
                duration: 4000,
                position: 'top-right',
            });
        } else {
            toast(data.message);
        }
    });
}
