<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Check, ClipboardList, PackageCheck } from '@lucide/vue';
import { computed } from 'vue';
import CompraController from '@/actions/App/Http/Controllers/Admin/Abastecimiento/CompraController';
import { ModuleHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useConfirm } from '@/composables/useConfirm';
import { useMoney } from '@/composables/useMoney';

type Purchase = {
    id: number;
    codigo: string;
    numero_factura: string | null;
    fecha_compra: string;
    subtotal: string;
    impuesto: string;
    total: string;
    estado: 'pendiente' | 'recibida' | 'cancelada';
    notas: string | null;
    recibida_at: string | null;
    cancelada_at: string | null;
    proveedor: { id: number; nombre: string; documento: string | null; contacto: string | null; telefono: string | null; email: string | null } | null;
    creador: { id: number; name: string } | null;
    detalles: Array<{ id: number; producto_id: number; producto_nombre: string; cantidad: number; precio_unitario: string; subtotal: string; producto: { sku: string | null } | null }>;
};

const props = defineProps<{ compra: Purchase; moneda: string }>();
const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const { confirm, success } = useConfirm();
const { format } = useMoney();
const stateStyles: Record<string, string> = {
    pendiente: 'bg-amber-100 text-amber-800',
    recibida: 'bg-green-100 text-green-800',
    cancelada: 'bg-gray-100 text-gray-600',
};
const stateLabels: Record<string, string> = {
    pendiente: 'Pendiente de recepción',
    recibida: 'Recibida',
    cancelada: 'Cancelada',
};

async function receivePurchase() {
    const confirmed = await confirm({
        title: 'Confirmar recepción',
        text: 'Al confirmar, las cantidades se sumarán al inventario y el costo de compra de cada producto se actualizará.',
        confirmText: 'Confirmar recepción',
    });

    if (confirmed) {
        router.post(CompraController.receive.url({ current_team: teamSlug.value, compra: props.compra.id }), {}, {
            preserveScroll: true,
            onSuccess: () => success('Inventario actualizado'),
        });
    }
}

async function cancelPurchase() {
    const confirmed = await confirm({
        title: 'Cancelar compra',
        text: 'La compra no afectará el inventario. Esta acción no se puede deshacer.',
        danger: true,
        confirmText: 'Cancelar compra',
    });

    if (confirmed) {
        router.post(CompraController.cancel.url({ current_team: teamSlug.value, compra: props.compra.id }), {}, {
            preserveScroll: true,
            onSuccess: () => success('Compra cancelada'),
        });
    }
}
</script>

<template>
    <Head :title="compra.codigo" />
    <div class="flex flex-col gap-6">
        <ModuleHeader :title="compra.codigo" description="Detalle de compra y estado de recepción" :icon="ClipboardList">
            <template #actions>
                <Link :href="CompraController.index.url(teamSlug)" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800">
                    <ArrowLeft class="h-4 w-4" /> Volver a compras
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <Badge :class="stateStyles[compra.estado]" class="rounded-md px-3 py-1 shadow-none">{{ stateLabels[compra.estado] }}</Badge>
            <div v-if="compra.estado === 'pendiente'" class="flex flex-wrap gap-2">
                <Button class="rounded-lg bg-green-600 text-white hover:bg-green-700" @click="receivePurchase">
                    <PackageCheck class="mr-2 h-4 w-4" /> Confirmar recepción
                </Button>
                <Button variant="outline" class="rounded-lg border-red-200 text-red-700 hover:bg-red-50" @click="cancelPurchase">
                    Cancelar compra
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 font-semibold text-gray-900"><Building2 class="h-4 w-4 text-blue-600" /> Proveedor</h2>
                    <div class="grid gap-3 text-sm sm:grid-cols-2">
                        <div><p class="text-xs text-gray-500">Nombre</p><p class="font-medium text-gray-900">{{ compra.proveedor?.nombre ?? 'Proveedor archivado' }}</p></div>
                        <div><p class="text-xs text-gray-500">Documento</p><p class="text-gray-700">{{ compra.proveedor?.documento || '—' }}</p></div>
                        <div><p class="text-xs text-gray-500">Contacto</p><p class="text-gray-700">{{ compra.proveedor?.contacto || '—' }}</p></div>
                        <div><p class="text-xs text-gray-500">Teléfono / correo</p><p class="text-gray-700">{{ compra.proveedor?.telefono || compra.proveedor?.email || '—' }}</p></div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="font-semibold text-gray-900">Productos</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <tr><th class="px-5 py-3">Producto</th><th class="px-5 py-3 text-right">Cantidad</th><th class="px-5 py-3 text-right">Costo unitario</th><th class="px-5 py-3 text-right">Subtotal</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="detalle in compra.detalles" :key="detalle.id" class="border-t border-gray-100">
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ detalle.producto_nombre }}<p v-if="detalle.producto?.sku" class="text-xs font-normal text-gray-500">{{ detalle.producto.sku }}</p></td>
                                    <td class="px-5 py-4 text-right text-gray-700">{{ detalle.cantidad }}</td>
                                    <td class="px-5 py-4 text-right text-gray-700">{{ format(detalle.precio_unitario) }}</td>
                                    <td class="px-5 py-4 text-right font-medium text-gray-900">{{ format(detalle.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-semibold text-gray-900">Resumen</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Fecha</dt><dd class="text-right text-gray-900">{{ new Date(`${compra.fecha_compra}T00:00:00`).toLocaleDateString('es-BO') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Factura</dt><dd class="text-right text-gray-900">{{ compra.numero_factura || '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Registrado por</dt><dd class="text-right text-gray-900">{{ compra.creador?.name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3 border-t border-gray-100 pt-3"><dt class="text-gray-500">Subtotal</dt><dd class="text-gray-900">{{ format(compra.subtotal) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Impuesto</dt><dd class="text-gray-900">{{ format(compra.impuesto) }}</dd></div>
                        <div class="flex justify-between gap-3 border-t border-gray-100 pt-3 font-semibold"><dt class="text-gray-900">Total</dt><dd class="text-gray-900">{{ format(compra.total) }}</dd></div>
                    </dl>
                    <p v-if="compra.recibida_at" class="mt-4 flex items-center gap-2 rounded-lg bg-green-50 p-3 text-xs text-green-800"><Check class="h-4 w-4" /> Recepción confirmada {{ new Date(compra.recibida_at).toLocaleString('es-BO') }}</p>
                    <p v-if="compra.cancelada_at" class="mt-4 text-xs text-gray-500">Cancelada {{ new Date(compra.cancelada_at).toLocaleString('es-BO') }}</p>
                </section>
                <section v-if="compra.notas" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-2 font-semibold text-gray-900">Notas</h2>
                    <p class="whitespace-pre-line text-sm text-gray-600">{{ compra.notas }}</p>
                </section>
            </aside>
        </div>
    </div>
</template>
