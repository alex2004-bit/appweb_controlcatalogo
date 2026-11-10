<script setup lang="ts">
import { Link, router, Head } from '@inertiajs/vue3';
import { ArrowLeft, Printer, Send, FileText, User, Building2, Mail, CreditCard, DollarSign } from '@lucide/vue';
import { computed } from 'vue';
import { StatusBadge } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useMoney } from '@/composables/useMoney';

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
    pendiente: 'bg-yellow-100 text-yellow-700',
    parcial: 'bg-blue-100 text-blue-700',
    pagado: 'bg-green-100 text-green-700',
};

const { format } = useMoney();

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
    <Head :title="cotizacion.codigo" />
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-600">
                    <FileText class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ cotizacion.codigo }}</h1>
                    <p class="text-sm text-gray-500">Creado: {{ cotizacion.created_at }} &middot; Válido por {{ cotizacion.validez_dias }} días</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.cotizaciones.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <StatusBadge :value="cotizacion.estado" />
                <Badge class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium" :class="estadoPagoColors[cotizacion.estado_pago] || 'bg-gray-100 text-gray-500'">
                    {{ estadoPagoLabels[cotizacion.estado_pago] || cotizacion.estado_pago }}
                </Badge>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="printPdf" class="rounded-lg border-gray-200 h-9"><Printer class="mr-2 h-4 w-4" /> Imprimir</Button>
                <Button v-if="cotizacion.estado === 'borrador'" size="sm" @click="updateEstado('enviada')" class="rounded-lg h-9 bg-blue-600 text-white hover:bg-blue-700 shadow-sm"><Send class="mr-2 h-4 w-4" /> Enviar</Button>
                <Button v-if="cotizacion.estado === 'enviada'" variant="default" size="sm" @click="updateEstado('aprobada')" class="rounded-lg h-9 bg-blue-600 text-white hover:bg-blue-700 shadow-sm">Aprobar</Button>
                <Button v-if="cotizacion.estado === 'enviada'" variant="destructive" size="sm" @click="updateEstado('rechazada')" class="rounded-lg h-9">Rechazar</Button>
                <Link :href="route('admin.cotizaciones.edit', cotizacion.id)">
                    <Button variant="outline" size="sm" class="rounded-lg border-gray-200 h-9">Editar</Button>
                </Link>
                <Link :href="route('admin.finanzas.pagos.create', { cotizacion_id: cotizacion.id })">
                    <Button size="sm" class="rounded-lg h-9 bg-blue-600 text-white hover:bg-blue-700 shadow-sm"><DollarSign class="mr-2 h-4 w-4" /> Registrar Pago</Button>
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
                        <div class="flex items-center gap-2.5 text-sm text-gray-900"><User class="h-4 w-4 text-gray-400 shrink-0" />{{ cotizacion.cliente?.nombre ?? '—' }}</div>
                        <div v-if="cotizacion.cliente?.documento" class="flex items-center gap-2.5 text-sm text-gray-900"><Building2 class="h-4 w-4 text-gray-400 shrink-0" />{{ cotizacion.cliente.documento }}</div>
                        <div v-if="cotizacion.cliente?.email" class="flex items-center gap-2.5 text-sm text-gray-900"><Mail class="h-4 w-4 text-gray-400 shrink-0" />{{ cotizacion.cliente.email }}</div>
                        <div v-if="cotizacion.cliente?.telefono" class="flex items-center gap-2.5 text-sm text-gray-900">{{ cotizacion.cliente.telefono }}</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-left font-medium text-gray-500">Descripción</th>
                                <th class="pb-3 text-center font-medium text-gray-500">Cant.</th>
                                <th class="pb-3 text-right font-medium text-gray-500">P. Unit.</th>
                                <th class="pb-3 text-right font-medium text-gray-500">Desc.</th>
                                <th class="pb-3 text-right font-medium text-gray-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in cotizacion.detalles" :key="d.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                                <td class="py-3.5 font-medium text-gray-900">{{ d.item_nombre }}</td>
                                <td class="py-3.5 text-center text-gray-500">{{ d.cantidad }}</td>
                                <td class="py-3.5 text-right text-gray-500">{{ format(d.precio_unitario) }}</td>
                                <td class="py-3.5 text-right text-gray-500">{{ format(d.descuento) }}</td>
                                <td class="py-3.5 text-right font-medium text-gray-900">{{ format(d.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 space-y-1.5 border-t border-gray-200 pt-4 text-right text-sm">
                    <div class="flex justify-end gap-8"><span class="text-gray-500">Subtotal:</span><span class="w-28 text-right text-gray-900">{{ format(cotizacion.subtotal) }}</span></div>
                    <div class="flex justify-end gap-8"><span class="text-gray-500">Impuesto:</span><span class="w-28 text-right text-gray-900">{{ format(cotizacion.impuesto) }}</span></div>
                    <div v-if="parseFloat(cotizacion.descuento) > 0" class="flex justify-end gap-8"><span class="text-gray-500">Descuento:</span><span class="w-28 text-right text-red-500">-{{ format(cotizacion.descuento) }}</span></div>
                    <div class="flex justify-end gap-8 text-lg font-bold border-t border-gray-200 pt-2 mt-2"><span class="text-gray-900">Total:</span><span class="w-28 text-right text-blue-600">{{ format(cotizacion.total) }}</span></div>
                </div>

                <div v-if="cotizacion.notas || cotizacion.condiciones" class="mt-8 grid gap-4 md:grid-cols-2">
                    <div v-if="cotizacion.notas" class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-2 text-xs font-semibold text-gray-500 uppercase">Notas</h4>
                        <p class="whitespace-pre-line text-sm leading-relaxed text-gray-900">{{ cotizacion.notas }}</p>
                    </div>
                    <div v-if="cotizacion.condiciones" class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-2 text-xs font-semibold text-gray-500 uppercase">Condiciones</h4>
                        <p class="text-sm leading-relaxed text-gray-900">{{ cotizacion.condiciones }}</p>
                    </div>
                </div>
            </div>

            <div v-if="cotizacion.pagos.length" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100">
                        <CreditCard class="h-4 w-4 text-blue-600" />
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Historial de Pagos</h2>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                    <div class="rounded-lg bg-gray-50 p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Total</p>
                        <p class="text-lg font-bold text-gray-900">{{ format(cotizacion.total) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Pagado</p>
                        <p class="text-lg font-bold text-green-600">{{ format(totalPagado) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Saldo Pendiente</p>
                        <p class="text-lg font-bold" :class="cotizacion.saldo_pendiente > 0 ? 'text-orange-500' : 'text-green-600'">
                            {{ format(cotizacion.saldo_pendiente) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left">
                                <th class="pb-3 font-medium text-gray-500">N°</th>
                                <th class="pb-3 font-medium text-gray-500">Fecha</th>
                                <th class="pb-3 font-medium text-gray-500">Método</th>
                                <th class="pb-3 font-medium text-gray-500">Referencia</th>
                                <th class="pb-3 font-medium text-gray-500 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pago in cotizacion.pagos" :key="pago.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                                <td class="py-3">
                                    <Link :href="route('admin.finanzas.pagos.show', pago.id)" class="text-blue-600 hover:underline font-medium">#{{ pago.id }}</Link>
                                </td>
                                <td class="py-3 text-gray-500">{{ pago.fecha_pago }}</td>
                                <td class="py-3 text-gray-500">{{ metodoPagoLabels[pago.metodo_pago] || pago.metodo_pago }}</td>
                                <td class="py-3 text-gray-500">{{ pago.referencia || '—' }}</td>
                                <td class="py-3 text-right font-medium text-gray-900">{{ format(pago.monto) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
