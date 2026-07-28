<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Printer, Send, FileText, User, Building2, Mail, CreditCard, DollarSign } from '@lucide/vue';
import { computed } from 'vue';
import { StatusBadge, PageTransition } from '@/components/shared';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    cotizacion: {
        id: number;
        codigo: string;
        subtotal: string;
        impuesto: string;
        descuento: string;
        total: string;
        estado: string;
        notas: string | null;
        condiciones: string | null;
        observaciones: string | null;
        validez_dias: number;
        created_at: string;
        saldo_pendiente: number;
        estado_pago: string;
        cliente: { id: number; nombre: string; documento: string | null; email: string | null; direccion: string | null; telefono: string | null } | null;
        creador: { id: number; name: string };
        detalles: Array<{
            id: number;
            item_type: string;
            item_nombre: string;
            cantidad: number;
            precio_unitario: string;
            descuento: string;
            subtotal: string;
        }>;
        pagos: Array<{
            id: number;
            monto: string;
            metodo_pago: string;
            referencia: string | null;
            estado: string;
            fecha_pago: string;
        }>;
    };
}>();

const estadoPagoColors: Record<string, string> = {
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    parcial: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    pagado: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
};

const estadoPagoLabels: Record<string, string> = {
    pendiente: 'Pendiente',
    parcial: 'Parcialmente Pagada',
    pagado: 'Pagada',
};

const metodoPagoLabels: Record<string, string> = {
    efectivo: 'Efectivo',
    transferencia: 'Transferencia',
    tarjeta: 'Tarjeta',
    cheque: 'Cheque',
    deposito: 'Depósito',
    otro: 'Otro',
};

const totalPagado = computed(() =>
    props.cotizacion.pagos.reduce((sum, p) => sum + parseFloat(p.monto), 0),
);

function printPdf() {
    window.print();
}

function updateEstado(estado: string) {
    router.put(route('admin.cotizaciones.update', props.cotizacion.id), {
        cliente_id: props.cotizacion.cliente?.id,
        estado,
        validez_dias: props.cotizacion.validez_dias,
        notas: props.cotizacion.notas,
        condiciones: props.cotizacion.condiciones,
        observaciones: props.cotizacion.observaciones,
        items: props.cotizacion.detalles.map(d => ({
            item_type: d.item_type.includes('Producto') ? 'producto' : 'servicio',
            item_id: 0,
            item_nombre: d.item_nombre,
            cantidad: d.cantidad,
            precio_unitario: parseFloat(d.precio_unitario),
            descuento: parseFloat(d.descuento) / d.cantidad,
        })),
    }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Clientes' }, { title: 'Cotizaciones', href: route('admin.cotizaciones.index') }, { title: cotizacion.codigo, href: '' }]">
        <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                        <FileText class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ cotizacion.codigo }}</h1>
                        <p class="text-sm text-muted-foreground">Creado: {{ cotizacion.created_at }} · Válido por {{ cotizacion.validez_dias }} días</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.cotizaciones.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <ArrowLeft class="h-4 w-4" /> Volver
                    </Link>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <StatusBadge :value="cotizacion.estado" />
                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="estadoPagoColors[cotizacion.estado_pago] || 'bg-gray-100'">
                        {{ estadoPagoLabels[cotizacion.estado_pago] || cotizacion.estado_pago }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="printPdf" class="rounded-xl h-9"><Printer class="mr-2 h-4 w-4" /> Imprimir</Button>
                    <Button v-if="cotizacion.estado === 'borrador'" size="sm" @click="updateEstado('enviada')" class="rounded-xl h-9 bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-600 hover:to-blue-700"><Send class="mr-2 h-4 w-4" /> Enviar</Button>
                    <Button v-if="cotizacion.estado === 'enviada'" variant="default" size="sm" @click="updateEstado('aprobada')" class="rounded-xl h-9 bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25">Aprobar</Button>
                    <Button v-if="cotizacion.estado === 'enviada'" variant="destructive" size="sm" @click="updateEstado('rechazada')" class="rounded-xl h-9">Rechazar</Button>
                    <Link :href="route('admin.cotizaciones.edit', cotizacion.id)">
                        <Button variant="outline" size="sm" class="rounded-xl h-9">Editar</Button>
                    </Link>
                    <Link :href="route('admin.finanzas.pagos.create', { cotizacion_id: cotizacion.id })">
                        <Button size="sm" class="rounded-xl h-9 bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-600 hover:to-blue-700"><DollarSign class="mr-2 h-4 w-4" /> Registrar Pago</Button>
                    </Link>
                </div>
            </div>

            <div class="mx-auto w-full max-w-4xl space-y-6">
                <div class="rounded-xl border border-border bg-card p-8 shadow-lg shadow-black/10">
                    <div class="mb-8 rounded-xl bg-background/50 p-5">
                        <h3 class="mb-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider flex items-center gap-2">
                            <User class="h-4 w-4 text-cyan-400" /> Cliente
                        </h3>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="flex items-center gap-2.5 text-sm"><User class="h-4 w-4 text-muted-foreground shrink-0" />{{ cotizacion.cliente?.nombre ?? '—' }}</div>
                            <div v-if="cotizacion.cliente?.documento" class="flex items-center gap-2.5 text-sm"><Building2 class="h-4 w-4 text-muted-foreground shrink-0" />{{ cotizacion.cliente.documento }}</div>
                            <div v-if="cotizacion.cliente?.email" class="flex items-center gap-2.5 text-sm"><Mail class="h-4 w-4 text-muted-foreground shrink-0" />{{ cotizacion.cliente.email }}</div>
                            <div v-if="cotizacion.cliente?.telefono" class="flex items-center gap-2.5 text-sm">{{ cotizacion.cliente.telefono }}</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="pb-3 text-left font-medium text-muted-foreground">Descripción</th>
                                    <th class="pb-3 text-center font-medium text-muted-foreground">Cant.</th>
                                    <th class="pb-3 text-right font-medium text-muted-foreground">P. Unit.</th>
                                    <th class="pb-3 text-right font-medium text-muted-foreground">Desc.</th>
                                    <th class="pb-3 text-right font-medium text-muted-foreground">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in cotizacion.detalles" :key="d.id" class="border-b border-border transition-colors hover:bg-accent/50">
                                    <td class="py-3.5 font-medium">{{ d.item_nombre }}</td>
                                    <td class="py-3.5 text-center">{{ d.cantidad }}</td>
                                    <td class="py-3.5 text-right">${{ parseFloat(d.precio_unitario).toFixed(2) }}</td>
                                    <td class="py-3.5 text-right">${{ parseFloat(d.descuento).toFixed(2) }}</td>
                                    <td class="py-3.5 text-right font-medium">${{ parseFloat(d.subtotal).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 space-y-1.5 border-t border-border pt-4 text-right text-sm">
                        <div class="flex justify-end gap-8"><span class="text-muted-foreground">Subtotal:</span><span class="w-28 text-right">${{ parseFloat(cotizacion.subtotal).toFixed(2) }}</span></div>
                        <div class="flex justify-end gap-8"><span class="text-muted-foreground">Impuesto (18%):</span><span class="w-28 text-right">${{ parseFloat(cotizacion.impuesto).toFixed(2) }}</span></div>
                        <div v-if="parseFloat(cotizacion.descuento) > 0" class="flex justify-end gap-8"><span class="text-muted-foreground">Descuento:</span><span class="w-28 text-right text-red-400">-${{ parseFloat(cotizacion.descuento).toFixed(2) }}</span></div>
                        <div class="flex justify-end gap-8 text-lg font-bold border-t border-border pt-2 mt-2"><span>Total:</span><span class="w-28 text-right text-cyan-400">${{ parseFloat(cotizacion.total).toFixed(2) }}</span></div>
                    </div>

                    <div v-if="cotizacion.notas || cotizacion.condiciones" class="mt-8 grid gap-4 md:grid-cols-2">
                        <div v-if="cotizacion.notas" class="rounded-xl bg-background/50 p-4">
                            <h4 class="mb-2 text-xs font-semibold text-muted-foreground uppercase">Notas</h4>
                            <p class="text-sm leading-relaxed">{{ cotizacion.notas }}</p>
                        </div>
                        <div v-if="cotizacion.condiciones" class="rounded-xl bg-background/50 p-4">
                            <h4 class="mb-2 text-xs font-semibold text-muted-foreground uppercase">Condiciones</h4>
                            <p class="text-sm leading-relaxed">{{ cotizacion.condiciones }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="cotizacion.pagos.length" class="rounded-xl border border-border bg-card p-6 shadow-lg shadow-black/10">
                    <div class="mb-5 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-600/20">
                            <CreditCard class="h-4 w-4 text-cyan-400" />
                        </div>
                        <h2 class="text-lg font-semibold">Historial de Pagos</h2>
                    </div>

                    <div class="mb-6 grid grid-cols-3 gap-4">
                        <div class="rounded-xl bg-background/50 p-4 text-center">
                            <p class="text-xs text-muted-foreground mb-1">Total</p>
                            <p class="text-lg font-bold">${{ parseFloat(cotizacion.total).toFixed(2) }}</p>
                        </div>
                        <div class="rounded-xl bg-background/50 p-4 text-center">
                            <p class="text-xs text-muted-foreground mb-1">Pagado</p>
                            <p class="text-lg font-bold text-green-500">${{ totalPagado.toFixed(2) }}</p>
                        </div>
                        <div class="rounded-xl bg-background/50 p-4 text-center">
                            <p class="text-xs text-muted-foreground mb-1">Saldo Pendiente</p>
                            <p class="text-lg font-bold" :class="cotizacion.saldo_pendiente > 0 ? 'text-orange-400' : 'text-green-500'">
                                ${{ cotizacion.saldo_pendiente.toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left">
                                    <th class="pb-3 font-medium text-muted-foreground">N°</th>
                                    <th class="pb-3 font-medium text-muted-foreground">Fecha</th>
                                    <th class="pb-3 font-medium text-muted-foreground">Método</th>
                                    <th class="pb-3 font-medium text-muted-foreground">Referencia</th>
                                    <th class="pb-3 font-medium text-muted-foreground text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pago in cotizacion.pagos" :key="pago.id" class="border-b border-border transition-colors hover:bg-accent/50">
                                    <td class="py-3">
                                        <Link :href="route('admin.finanzas.pagos.show', pago.id)" class="text-primary hover:underline font-medium">#{{ pago.id }}</Link>
                                    </td>
                                    <td class="py-3">{{ pago.fecha_pago }}</td>
                                    <td class="py-3">{{ metodoPagoLabels[pago.metodo_pago] || pago.metodo_pago }}</td>
                                    <td class="py-3">{{ pago.referencia || '—' }}</td>
                                    <td class="py-3 text-right font-medium">${{ parseFloat(pago.monto).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </PageTransition>
    </AppLayout>
</template>
