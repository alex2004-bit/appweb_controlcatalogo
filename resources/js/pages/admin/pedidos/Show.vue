<script setup lang="ts">
import { Link, router, Head } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingCart, User, Building2, Mail, CreditCard, Package, Wrench, Truck, UserRound, CalendarDays } from '@lucide/vue';
import type { AcceptableValue } from 'reka-ui';
import { StatusBadge } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useMoney } from '@/composables/useMoney';

const props = defineProps<{
    pedido: {
        id: number;
        codigo: string;
        subtotal: string;
        impuesto: string;
        descuento: string;
        total: string;
        anticipo: string;
        numero_cuotas: number;
        plazo_dias: number | null;
        estado: string;
        pagado: boolean;
        metodo_pago: string | null;
        notas: string | null;
        direccion_entrega: string | null;
        ciudad: string | null;
        provincia: string | null;
        tipo_entrega: string | null;
        fecha_entrega: string | null;
        telefono_contacto: string | null;
        referido_por: string | null;
        created_at: string;
        completado_at: string | null;
        saldo_pendiente: number;
        estado_pago: string;
        cliente: { id: number; nombre: string; documento: string | null; email: string | null; telefono: string | null } | null;
        creador: { id: number; name: string };
        vendedor: { id: number; name: string } | null;
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
    pendiente: 'bg-yellow-100 text-yellow-700',
    procesando: 'bg-blue-100 text-blue-700',
    completado: 'bg-green-100 text-green-700',
    cancelado: 'bg-red-100 text-red-700',
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

const { format } = useMoney();

function updateEstado(estado: AcceptableValue) {
    if (estado === null || estado === undefined) {
        return;
    }

    router.put(route('admin.pedidos.update', props.pedido.id), {
        cliente_id: props.pedido.cliente?.id,
        estado: String(estado),
        vendedor_id: props.pedido.vendedor?.id,
        referido_por: props.pedido.referido_por,
        metodo_pago: props.pedido.metodo_pago,
        pagado: props.pedido.pagado,
        notas: props.pedido.notas,
        direccion_entrega: props.pedido.direccion_entrega,
        ciudad: props.pedido.ciudad,
        provincia: props.pedido.provincia,
        tipo_entrega: props.pedido.tipo_entrega,
        fecha_entrega: props.pedido.fecha_entrega,
        telefono_contacto: props.pedido.telefono_contacto,
        anticipo: props.pedido.anticipo,
        numero_cuotas: props.pedido.numero_cuotas,
        plazo_dias: props.pedido.plazo_dias,
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
    <Head :title="pedido.codigo" />
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-600">
                    <ShoppingCart class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ pedido.codigo }}</h1>
                    <p class="text-sm text-gray-500">Creado: {{ new Date(pedido.created_at).toLocaleDateString() }} &middot; {{ pedido.creador?.name ?? '—' }}</p>
                </div>
            </div>
            <Link :href="route('admin.pedidos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <ArrowLeft class="h-4 w-4" /> Volver
            </Link>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Badge class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium" :class="estadoColors[pedido.estado] || 'bg-gray-100 text-gray-500'">
                    {{ estadoLabels[pedido.estado] || pedido.estado }}
                </Badge>
                <StatusBadge :value="pedido.pagado ? 'pagado' : 'pendiente'" />
            </div>
            <div class="flex items-center gap-2">
                <Select v-if="pedido.estado !== 'completado' && pedido.estado !== 'cancelado'" @update:model-value="updateEstado">
                    <SelectTrigger class="h-9 rounded-lg border-gray-300 w-40">
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
                    <Button variant="outline" size="sm" class="rounded-lg border-gray-200 h-9">Editar</Button>
                </Link>
            </div>
        </div>

        <div class="mx-auto w-full max-w-4xl space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                <div class="mb-8 rounded-lg bg-gray-50 p-5">
                    <h3 class="mb-3 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <User class="h-4 w-4 text-blue-500" /> Cliente
                    </h3>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="flex items-center gap-2.5 text-sm text-gray-900"><User class="h-4 w-4 text-gray-400 shrink-0" />{{ pedido.cliente?.nombre ?? '—' }}</div>
                        <div v-if="pedido.cliente?.documento" class="flex items-center gap-2.5 text-sm text-gray-900"><Building2 class="h-4 w-4 text-gray-400 shrink-0" />{{ pedido.cliente.documento }}</div>
                        <div v-if="pedido.cliente?.email" class="flex items-center gap-2.5 text-sm text-gray-900"><Mail class="h-4 w-4 text-gray-400 shrink-0" />{{ pedido.cliente.email }}</div>
                        <div v-if="pedido.cliente?.telefono" class="flex items-center gap-2.5 text-sm text-gray-900">{{ pedido.cliente.telefono }}</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-left font-medium text-gray-500">Tipo</th>
                                <th class="pb-3 text-left font-medium text-gray-500">Descripción</th>
                                <th class="pb-3 text-center font-medium text-gray-500">Cant.</th>
                                <th class="pb-3 text-right font-medium text-gray-500">P. Unit.</th>
                                <th class="pb-3 text-right font-medium text-gray-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in pedido.detalles" :key="d.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                                <td class="py-3.5">
                                    <component :is="d.item_type.includes('Producto') ? Package : Wrench" class="h-4 w-4 text-gray-400" />
                                </td>
                                <td class="py-3.5 font-medium text-gray-900">{{ d.item_nombre }}</td>
                                <td class="py-3.5 text-center text-gray-500">{{ d.cantidad }}</td>
                                <td class="py-3.5 text-right text-gray-500">{{ format(d.precio_unitario) }}</td>
                                <td class="py-3.5 text-right font-medium text-gray-900">{{ format(d.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 space-y-1.5 border-t border-gray-200 pt-4 text-right text-sm">
                    <div class="flex justify-end gap-8"><span class="text-gray-500">Subtotal:</span><span class="w-28 text-right text-gray-900">{{ format(pedido.subtotal) }}</span></div>
                    <div class="flex justify-end gap-8"><span class="text-gray-500">Impuesto:</span><span class="w-28 text-right text-gray-900">{{ format(pedido.impuesto) }}</span></div>
                    <div v-if="parseFloat(pedido.descuento) > 0" class="flex justify-end gap-8"><span class="text-gray-500">Descuento:</span><span class="w-28 text-right text-red-500">-{{ format(pedido.descuento) }}</span></div>
                    <div class="flex justify-end gap-8 text-lg font-bold border-t border-gray-200 pt-2 mt-2"><span class="text-gray-900">Total:</span><span class="w-28 text-right text-blue-600">{{ format(pedido.total) }}</span></div>
                </div>

                <div v-if="pedido.metodo_pago" class="mt-6 flex items-center gap-2 rounded-lg bg-gray-50 p-3 text-sm">
                    <CreditCard class="h-4 w-4 text-blue-500" />
                    <span class="text-gray-500">Método de pago:</span>
                    <span class="font-medium text-gray-900">{{ metodoPagoLabels[pedido.metodo_pago] || pedido.metodo_pago }}</span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <Truck class="h-4 w-4 text-blue-500" /> Entrega
                        </h4>
                        <p v-if="pedido.direccion_entrega" class="text-sm text-gray-900">{{ pedido.direccion_entrega }}</p>
                        <p v-if="pedido.ciudad || pedido.provincia" class="mt-1 text-sm text-gray-700">{{ [pedido.ciudad, pedido.provincia].filter(Boolean).join(', ') }}</p>
                        <p v-if="pedido.tipo_entrega" class="mt-1 text-sm text-gray-500">
                            {{ pedido.tipo_entrega === 'provincia' ? 'Envío a provincia' : 'Recoger en tienda' }}
                        </p>
                        <p v-if="pedido.fecha_entrega" class="mt-1 flex items-center gap-1.5 text-sm text-gray-700">
                            <CalendarDays class="h-4 w-4 text-gray-400" />{{ new Date(pedido.fecha_entrega).toLocaleString() }}
                        </p>
                        <p v-if="pedido.telefono_contacto" class="mt-1 text-sm text-gray-700">{{ pedido.telefono_contacto }}</p>
                        <p v-if="!pedido.direccion_entrega && !pedido.ciudad && !pedido.provincia && !pedido.fecha_entrega && !pedido.telefono_contacto" class="text-sm text-gray-400">Sin datos de entrega</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <UserRound class="h-4 w-4 text-blue-500" /> Vendedor
                        </h4>
                        <p class="text-sm text-gray-900">{{ pedido.vendedor?.name ?? '—' }}</p>
                        <p v-if="pedido.referido_por" class="mt-1 text-sm text-gray-500">Referido por: {{ pedido.referido_por }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <CreditCard class="h-4 w-4 text-blue-500" /> Condiciones
                        </h4>
                        <p class="text-sm text-gray-900">Anticipo: <span class="font-medium">{{ format(pedido.anticipo) }}</span></p>
                        <p class="mt-1 text-sm text-gray-700">Saldo pendiente: <span class="font-medium">{{ format(pedido.saldo_pendiente) }}</span></p>
                        <p v-if="pedido.numero_cuotas > 1" class="mt-1 text-sm text-gray-700">{{ pedido.numero_cuotas }} cuotas</p>
                        <p v-if="pedido.plazo_dias" class="mt-1 text-sm text-gray-700">Crédito: {{ pedido.plazo_dias }} días</p>
                    </div>
                </div>

                <div v-if="pedido.notas" class="mt-4 rounded-lg bg-gray-50 p-4">
                    <h4 class="mb-2 text-xs font-semibold text-gray-500 uppercase">Notas</h4>
                    <p class="text-sm leading-relaxed text-gray-900">{{ pedido.notas }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
