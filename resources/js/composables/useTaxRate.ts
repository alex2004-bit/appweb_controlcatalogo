import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useTaxRate() {
    const page = usePage();

    const rate = computed(() => {
        const configuracion = page.props.configuracion;

        if (typeof configuracion !== 'object' || configuracion === null || !('impuesto' in configuracion)) {
            return 18;
        }

        const configuredRate = Number(configuracion.impuesto);

        return Number.isFinite(configuredRate) ? configuredRate : 18;
    });

    return rate;
}
