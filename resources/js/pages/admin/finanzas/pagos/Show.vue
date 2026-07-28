<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Printer, Download, CreditCard, User, Building2, Calendar, FileText } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PageTransition } from '@/components/shared';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    pago: {
        id: number;
        monto: string;
        metodo_pago: string;
        referencia: string | null;
        estado: string;
        fecha_pago: string;
        notas: string | null;
        comprobante: string | null;
        created_at: string;
        cotizacion: {
            id: number;
            codigo: string;
            total: string;
            subtotal: string;
            impuesto: string;
            descuento: string;
            notas: string | null;
            condiciones: string | null;
            created_at: string;
            cliente: { id: number; nombre: string; documento: string | null; direccion: string | null; telefono: string | null } | null;
            detalles: Array<{
                id: number;
                item_nombre: string;
                cantidad: number;
                precio_unitario: string;
                descuento: string;
                subtotal: string;
            }>;
        };
    };
    esUltimoPago: boolean;
}>();

const metodoPagoLabels: Record<string, string> = {
    efectivo: 'Efectivo',
    transferencia: 'Transferencia',
    tarjeta: 'Tarjeta',
    cheque: 'Cheque',
    deposito: 'Depósito',
    otro: 'Otro',
};

const estadoColors: Record<string, string> = {
    completado: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    rechazado: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

function downloadPdf() {
    window.open(route('admin.finanzas.pagos.pdf', props.pago.id), '_blank');
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Finanzas' }, { title: 'Pagos', href: route('admin.finanzas.pagos.index') }, { title: 'Recibo #' + pago.id, href: '' }]">
        <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                        <CreditCard class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Recibo de Pago #{{ pago.id }}</h1>
                        <p class="text-sm text-muted-foreground">Registrado: {{ pago.created_at }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.finanzas.pagos.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <ArrowLeft class="h-4 w-4" /> Volver a Pagos
                    </Link>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <Button variant="outline" size="sm" @click="downloadPdf" class="rounded-xl h-9"><Download class="mr-2 h-4 w-4" /> Descargar Recibo PDF</Button>
                <Link v-if="esUltimoPago" :href="route('admin.finanzas.pagos.edit', pago.id)">
                    <Button variant="outline" size="sm" class="rounded-xl h-9">Editar</Button>
                </Link>
            </div>

            <div class="mx-auto w-full max-w-4xl space-y-6">
                <div class="rounded-xl border border-border bg-card p-8 shadow-lg shadow-black/10">
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <h1 class="text-2xl font-bold">Recibo de Pago #{{ pago.id }}</h1>
                            <p class="mt-1 text-sm text-muted-foreground">Registrado: {{ pago.created_at }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-medium" :class="estadoColors[pago.estado] || 'bg-gray-100'">
                            {{ pago.estado }}
                        </span>
                    </div>

                    <div class="mb-8 grid gap-6 md:grid-cols-2">
                        <div class="rounded-xl bg-background/50 p-5">
                            <h3 class="mb-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider flex items-center gap-2">
                                <CreditCard class="h-4 w-4 text-cyan-400" /> Información del Pago
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center gap-2.5"><CreditCard class="h-4 w-4 text-muted-foreground shrink-0" /> <strong>Monto:</strong> <span class="text-cyan-400 font-medium">${{ parseFloat(pago.monto).toFixed(2) }}</span></div>
                                <div class="flex items-center gap-2.5"><CreditCard class="h-4 w-4 text-muted-foreground shrink-0" /> <strong>Método:</strong> {{ metodoPagoLabels[pago.metodo_pago] || pago.metodo_pago }}</div>
                                <div v-if="pago.referencia" class="flex items-center gap-2.5"><FileText class="h-4 w-4 text-muted-foreground shrink-0" /> <strong>Referencia:</strong> {{ pago.referencia }}</div>
                                <div class="flex items-center gap-2.5"><Calendar class="h-4 w-4 text-muted-foreground shrink-0" /> <strong>Fecha:</strong> {{ pago.fecha_pago }}</div>
                            </div>
                        </div>
                        <div class="rounded-xl bg-background/50 p-5">
                            <h3 class="mb-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider flex items-center gap-2">
                                <FileText class="h-4 w-4 text-cyan-400" /> Cotización
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div><strong>Código:</strong>
                                    <Link :href="route('admin.cotizaciones.show', pago.cotizacion.id)" class="text-primary hover:underline ml-1">
                                        {{ pago.cotizacion.codigo }}
                                    </Link>
                                </div>
                                <div class="flex items-center gap-2.5"><User class="h-4 w-4 text-muted-foreground shrink-0" /> <strong>Cliente:</strong> {{ pago.cotizacion.cliente?.nombre ?? '—' }}</div>
                                <div v-if="pago.cotizacion.cliente?.documento" class="flex items-center gap-2.5"><Building2 class="h-4 w-4 text-muted-foreground shrink-0" /> <strong>Documento:</strong> {{ pago.cotizacion.cliente.documento }}</div>
                                <div><strong>Total:</strong> <span class="text-cyan-400 font-medium">${{ parseFloat(pago.cotizacion.total).toFixed(2) }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="pago.notas" class="mb-6 rounded-xl bg-background/50 p-5">
                        <h3 class="mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Notas</h3>
                        <p class="text-sm leading-relaxed">{{ pago.notas }}</p>
                    </div>

                    <div v-if="pago.comprobante" class="rounded-xl bg-background/50 p-5">
                        <h3 class="mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Comprobante</h3>
                        <Link :href="'/storage/' + pago.comprobante" target="_blank" class="inline-flex items-center gap-2 text-sm text-cyan-400 hover:text-cyan-300 transition-colors">
                            <Printer class="h-4 w-4" /> Ver comprobante adjunto
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        </PageTransition>
    </AppLayout>
</template>
