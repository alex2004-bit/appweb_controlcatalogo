<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import { ArrowLeft, Download, CreditCard } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useMoney } from '@/composables/useMoney';

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
    completado: 'bg-green-100 text-green-700',
    pendiente: 'bg-yellow-100 text-yellow-700',
    rechazado: 'bg-red-100 text-red-700',
};

const { format } = useMoney();

function downloadPdf() {
    window.open(route('admin.finanzas.pagos.pdf', props.pago.id), '_blank');
}
</script>

<template>
    <Head title="Recibo de Pago" />
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-600">
                    <CreditCard class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Recibo de Pago #{{ pago.id }}</h1>
                    <p class="text-sm text-gray-500">Registrado: {{ pago.created_at }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.finanzas.pagos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowLeft class="h-4 w-4" /> Volver a Pagos
                </Link>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2">
            <Button variant="outline" size="sm" @click="downloadPdf" class="rounded-lg border-gray-200 h-9"><Download class="mr-2 h-4 w-4" /> Descargar Recibo PDF</Button>
            <Link v-if="esUltimoPago" :href="route('admin.finanzas.pagos.edit', pago.id)">
                <Button variant="outline" size="sm" class="rounded-lg border-gray-200 h-9">Editar</Button>
            </Link>
        </div>

        <div class="mx-auto w-full max-w-4xl space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Recibo de Pago #{{ pago.id }}</h1>
                        <p class="mt-1 text-sm text-gray-500">Registrado: {{ pago.created_at }}</p>
                    </div>
                    <Badge class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium" :class="estadoColors[pago.estado] || 'bg-gray-100 text-gray-500'">
                        {{ pago.estado }}
                    </Badge>
                </div>

                <div class="mb-8 grid gap-6 md:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 p-5">
                        <h3 class="mb-4 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <CreditCard class="h-4 w-4 text-blue-500" /> Información del Pago
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center gap-2.5"><CreditCard class="h-4 w-4 text-gray-400 shrink-0" /> <strong class="text-gray-900">Monto:</strong> <span class="text-blue-600 font-medium">{{ format(pago.monto) }}</span></div>
                            <div class="flex items-center gap-2.5"><CreditCard class="h-4 w-4 text-gray-400 shrink-0" /> <strong class="text-gray-900">Método:</strong> <span class="text-gray-900">{{ metodoPagoLabels[pago.metodo_pago] || pago.metodo_pago }}</span></div>
                            <div v-if="pago.referencia" class="flex items-center gap-2.5"><FileText class="h-4 w-4 text-gray-400 shrink-0" /> <strong class="text-gray-900">Referencia:</strong> <span class="text-gray-900">{{ pago.referencia }}</span></div>
                            <div class="flex items-center gap-2.5"><Calendar class="h-4 w-4 text-gray-400 shrink-0" /> <strong class="text-gray-900">Fecha:</strong> <span class="text-gray-900">{{ pago.fecha_pago }}</span></div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-5">
                        <h3 class="mb-4 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <FileText class="h-4 w-4 text-blue-500" /> Cotización
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div><strong class="text-gray-900">Código:</strong>
                                <Link :href="route('admin.cotizaciones.show', pago.cotizacion.id)" class="text-blue-600 hover:underline ml-1">
                                    {{ pago.cotizacion.codigo }}
                                </Link>
                            </div>
                            <div class="flex items-center gap-2.5"><User class="h-4 w-4 text-gray-400 shrink-0" /> <strong class="text-gray-900">Cliente:</strong> <span class="text-gray-900">{{ pago.cotizacion.cliente?.nombre ?? '—' }}</span></div>
                            <div v-if="pago.cotizacion.cliente?.documento" class="flex items-center gap-2.5"><Building2 class="h-4 w-4 text-gray-400 shrink-0" /> <strong class="text-gray-900">Documento:</strong> <span class="text-gray-900">{{ pago.cotizacion.cliente.documento }}</span></div>
                            <div><strong class="text-gray-900">Total:</strong> <span class="text-blue-600 font-medium">{{ format(pago.cotizacion.total) }}</span></div>
                        </div>
                    </div>
                </div>

                <div v-if="pago.notas" class="mb-6 rounded-lg bg-gray-50 p-5">
                    <h3 class="mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas</h3>
                    <p class="text-sm leading-relaxed text-gray-900">{{ pago.notas }}</p>
                </div>

                <div v-if="pago.comprobante" class="rounded-lg bg-gray-50 p-5">
                    <h3 class="mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Comprobante</h3>
                    <Link :href="'/storage/' + pago.comprobante" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 transition-colors">
                        <Printer class="h-4 w-4" /> Ver comprobante adjunto
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
