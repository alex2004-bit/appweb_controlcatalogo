<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ClipboardList, Eye, Plus, Search, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import CompraController from '@/actions/App/Http/Controllers/Admin/Abastecimiento/CompraController';
import { ModuleHeader, Pagination } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useMoney } from '@/composables/useMoney';
import type { PaginatedData } from '@/types';

type Purchase = {
    id: number;
    codigo: string;
    numero_factura: string | null;
    fecha_compra: string;
    total: string;
    estado: 'pendiente' | 'recibida' | 'cancelada';
    proveedor: { id: number; nombre: string } | null;
};

const props = defineProps<{
    compras: PaginatedData<Purchase>;
    filters: { search: string | null; estado: string | null };
    moneda: string;
}>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const search = ref(props.filters.search ?? '');
const estado = ref(props.filters.estado ?? 'all');
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

let searchTimer: number | undefined;
function applyFilters() {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
        router.get(CompraController.index.url(teamSlug.value), {
            search: search.value || null,
            estado: estado.value === 'all' ? null : estado.value,
        }, { preserveState: true, replace: true });
    }, 350);
}

function clearFilters() {
    search.value = '';
    estado.value = 'all';
    router.get(CompraController.index.url(teamSlug.value), {}, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Compras" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Compras" :description="`${compras.total} compras registradas`" :icon="ClipboardList">
            <template #actions>
                <Link :href="CompraController.create.url(teamSlug)">
                    <Button class="h-10 rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700">
                        <Plus class="mr-2 h-4 w-4" /> Registrar compra
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative min-w-[200px] max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <Input v-model="search" placeholder="Buscar código, factura o proveedor..." class="h-10 rounded-lg border-gray-300 pl-10 text-sm" @input="applyFilters" />
            </div>
            <select v-model="estado" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm" @change="applyFilters">
                <option value="all">Todos los estados</option>
                <option value="pendiente">Pendientes</option>
                <option value="recibida">Recibidas</option>
                <option value="cancelada">Canceladas</option>
            </select>
            <Button v-if="search || estado !== 'all'" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500" @click="clearFilters">
                <X class="mr-1.5 h-4 w-4" /> Limpiar
            </Button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Código / factura</th>
                            <th class="px-5 py-3">Proveedor</th>
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="compra in compras.data" :key="compra.id" class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <p class="font-mono font-medium text-gray-900">{{ compra.codigo }}</p>
                                <p class="text-xs text-gray-500">{{ compra.numero_factura || 'Sin número de factura' }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ compra.proveedor?.nombre ?? 'Proveedor archivado' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ new Date(`${compra.fecha_compra}T00:00:00`).toLocaleDateString('es-BO') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-900">{{ format(compra.total) }}</td>
                            <td class="px-5 py-4">
                                <Badge :class="stateStyles[compra.estado]" class="rounded-md shadow-none">{{ stateLabels[compra.estado] }}</Badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link :href="CompraController.show.url({ current_team: teamSlug, compra: compra.id })">
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg p-0" :aria-label="`Ver ${compra.codigo}`">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!compras.data.length">
                            <td colspan="6" class="px-5 py-16 text-center text-gray-500">
                                <ClipboardList class="mx-auto mb-3 h-10 w-10 text-gray-300" />
                                <p class="font-medium text-gray-900">Aún no hay compras registradas</p>
                                <p class="mt-1 text-sm">Registra una compra y confirma su recepción para actualizar el inventario.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Pagination :pagination="compras" />
    </div>
</template>
