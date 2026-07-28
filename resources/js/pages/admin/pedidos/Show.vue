<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingCart, User, Building2, Mail, CreditCard, Package, Wrench } from '@lucide/vue';
import { computed } from 'vue';
import { StatusBadge, PageTransition } from '@/components/shared';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    pedido: {
        id: number;
        codigo: string;
        subtotal: string;
        impuesto: string;
        descuento: string;
        total: string;
        estado: string;
        pagado: boolean;
        metodo_pago: string | null;
        notas: string | null;
        created_at: string;
        completado_at: string | null;
        saldo_pendiente: number;
        estado_pago: string;
        cliente: { id: number; nombre: string; documento: string | null; email: string | null; telefono: string | null } | null;
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
    };
}>();

const estadoColors: Record<string, string> = {
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    procesando: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    completado: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    cancelado: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

const estadoLabels: Record<string, string> = {
    pendiente: 'Pendiente',
    procesando: 'Procesando',
    completado: 'Completado',
    cancelado: 'Cancelado',
};

const metodoPagoLabels: Record<string, string> = {
    efectivo: 'Efectivo',
    transferencia: 'Transferencia',
    tarjeta: 'Tarjeta',
    cheque: 'Cheque',
    deposito: 'Depósito',
    otro: 'Otro',
};

function updateEstado(estado: string) {
    router.put(route('admin.pedidos.update', props.pedido.id), {
        cliente_id: props.pedido.cliente?.id,
        estado,
        metodo_pago: props.pedido.metodo_pago,
        pagado: props.pedido.pagado,
        notas: props.pedido.notas,
        items: props.pedido.detalles.map(d => ({
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
    <AppLayout :breadcrumbs="[{ title: 'Ventas' }, { title: 'Pedidos', href: route('admin.pedidos.index') }, { title: pedido.codigo, href: '' }]">
        <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                        <ShoppingCart class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ pedido.codigo }}</h1>
                        <p class="text-sm text-muted-foreground">Creado: {{ new Date(pedido.created_at).toLocaleDateString() }} · {{ pedido.creador.name }}</p>
                    </div>
                </div>
                <Link :href="route('admin.pedidos.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="estadoColors[pedido.estado] || ''">
                        {{ estadoLabels[pedido.estado] || pedido.estado }}
                    </span>
                    <StatusBadge :value="pedido.pagado ? 'pagado' : 'pendiente'" />
                </div>
                <div class="flex items-center gap-2">
                    <Select v-if="pedido.estado !== 'completado' && pedido.estado !== 'cancelado'" @update:model-value="updateEstado">
                        <SelectTrigger class="h-9 rounded-xl w-40">
                            <SelectValue placeholder="Cambiar estado..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pendiente">Pendiente</SelectItem>
                            <SelectItem value="procesando">Procesando</SelectItem>
                            <SelectItem value="completado">Completado</SelectItem>
                            <SelectItem value="cancelado">Cancelado</SelectItem>
                        </SelectContent>
                    </Select>
                    <Link :href="route('admin.pedidos.edit', pedido.id)">
                        <Button variant="outline" size="sm" class="rounded-xl h-9">Editar</Button>
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
                            <div class="flex items-center gap-2.5 text-sm"><User class="h-4 w-4 text-muted-foreground shrink-0" />{{ pedido.cliente?.nombre ?? '—' }}</div>
                            <div v-if="pedido.cliente?.documento" class="flex items-center gap-2.5 text-sm"><Building2 class="h-4 w-4 text-muted-foreground shrink-0" />{{ pedido.cliente.documento }}</div>
                            <div v-if="pedido.cliente?.email" class="flex items-center gap-2.5 text-sm"><Mail class="h-4 w-4 text-muted-foreground shrink-0" />{{ pedido.cliente.email }}</div>
                            <div v-if="pedido.cliente?.telefono" class="flex items-center gap-2.5 text-sm">{{ pedido.cliente.telefono }}</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="pb-3 text-left font-medium text-muted-foreground">Tipo</th>
                                    <th class="pb-3 text-left font-medium text-muted-foreground">Descripción</th>
                                    <th class="pb-3 text-center font-medium text-muted-foreground">Cant.</th>
                                    <th class="pb-3 text-right font-medium text-muted-foreground">P. Unit.</th>
                                    <th class="pb-3 text-right font-medium text-muted-foreground">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in pedido.detalles" :key="d.id" class="border-b border-border transition-colors hover:bg-accent/50">
                                    <td class="py-3.5">
                                        <component :is="d.item_type.includes('Producto') ? Package : Wrench" class="h-4 w-4 text-muted-foreground" />
                                    </td>
                                    <td class="py-3.5 font-medium">{{ d.item_nombre }}</td>
                                    <td class="py-3.5 text-center">{{ d.cantidad }}</td>
                                    <td class="py-3.5 text-right">${{ parseFloat(d.precio_unitario).toFixed(2) }}</td>
                                    <td class="py-3.5 text-right font-medium">${{ parseFloat(d.subtotal).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 space-y-1.5 border-t border-border pt-4 text-right text-sm">
                        <div class="flex justify-end gap-8"><span class="text-muted-foreground">Subtotal:</span><span class="w-28 text-right">${{ parseFloat(pedido.subtotal).toFixed(2) }}</span></div>
                        <div class="flex justify-end gap-8"><span class="text-muted-foreground">Impuesto (18%):</span><span class="w-28 text-right">${{ parseFloat(pedido.impuesto).toFixed(2) }}</span></div>
                        <div v-if="parseFloat(pedido.descuento) > 0" class="flex justify-end gap-8"><span class="text-muted-foreground">Descuento:</span><span class="w-28 text-right text-red-400">-${{ parseFloat(pedido.descuento).toFixed(2) }}</span></div>
                        <div class="flex justify-end gap-8 text-lg font-bold border-t border-border pt-2 mt-2"><span>Total:</span><span class="w-28 text-right text-cyan-400">${{ parseFloat(pedido.total).toFixed(2) }}</span></div>
                    </div>

                    <div v-if="pedido.metodo_pago" class="mt-6 flex items-center gap-2 rounded-xl bg-background/50 p-3 text-sm">
                        <CreditCard class="h-4 w-4 text-cyan-400" />
                        <span class="text-muted-foreground">Método de pago:</span>
                        <span class="font-medium">{{ metodoPagoLabels[pedido.metodo_pago] || pedido.metodo_pago }}</span>
                    </div>

                    <div v-if="pedido.notas" class="mt-4 rounded-xl bg-background/50 p-4">
                        <h4 class="mb-2 text-xs font-semibold text-muted-foreground uppercase">Notas</h4>
                        <p class="text-sm leading-relaxed">{{ pedido.notas }}</p>
                    </div>
                </div>
            </div>
        </div>
        </PageTransition>
    </AppLayout>
</template>
