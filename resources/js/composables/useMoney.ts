import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Configuracion = {
    qr_pago: string | null;
    whatsapp: string | null;
    moneda: string;
    impuesto: number;
};

export function useMoney() {
    const configuracion = computed<Configuracion>(
        () => (usePage().props.configuracion as Configuracion) ?? { qr_pago: null, whatsapp: null, moneda: 'Bs', impuesto: 18 },
    );

    const symbol = computed(() => (configuracion.value.moneda ?? 'Bs').trim() || 'Bs');

    function format(amount: number | string | null | undefined): string {
    const value = typeof amount === 'string' ? parseFloat(amount) : (amount ?? 0);

    if (isNaN(value)) {
return `${symbol.value} 0.00`;
}

    return `${symbol.value} ${value.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
}

    return { symbol, format };
}