<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    value: string;
    mapping?: Record<string, { label: string; variant: string }>;
}>();

const defaultMapping: Record<string, { label: string; variant: string }> = {
    activo: { label: 'Activo', variant: 'default' },
    inactivo: { label: 'Inactivo', variant: 'secondary' },
    pendiente: { label: 'Pendiente', variant: 'secondary' },
    aprobado: { label: 'Aprobado', variant: 'default' },
    rechazado: { label: 'Rechazado', variant: 'destructive' },
    borrador: { label: 'Borrador', variant: 'outline' },
    leido: { label: 'Leído', variant: 'default' },
    no_leido: { label: 'No leído', variant: 'secondary' },
    pagado: { label: 'Pagado', variant: 'default' },
    vencido: { label: 'Vencido', variant: 'destructive' },
    completado: { label: 'Completado', variant: 'default' },
};

const resolved = computed(() => {
    const map = props.mapping ?? defaultMapping;

    return map[props.value] ?? { label: props.value, variant: 'outline' };
});
</script>

<template>
    <Badge :variant="(resolved.variant as any)">
        {{ resolved.label }}
    </Badge>
</template>
