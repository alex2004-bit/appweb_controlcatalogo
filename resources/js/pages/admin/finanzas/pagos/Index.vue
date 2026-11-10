<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CreditCard, Plus, Eye, Pencil, Trash2, Search, X } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Pagination, ModuleHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useConfirm } from '@/composables/useConfirm';
import { useMoney } from '@/composables/useMoney';
import type { PaginatedData } from '@/types';

type PagoItem = { id: number; monto: string; metodo_pago: string; referencia: string | null; estado: string; fecha_pago: string; notas: string | null; comprobante: string | null; cotizacion: { id: number; codigo: string; total: string; cliente: { id: number; nombre: string } | null } | null };
const props = defineProps<{
    pagos: PaginatedData<PagoItem>;
    filters: { search: string | null; metodo_pago: string | null; estado: string | null; fecha_desde: string | null; fecha_hasta: string | null; };
}>();
const { confirm, success } = useConfirm();
const { format } = useMoney();

const search = ref(props.filters.search ?? '');
const metodoPago = ref(props.filters.metodo_pago ?? 'all');
const estadoFilter = ref(props.filters.estado ?? 'all');
const fechaDesde = ref(props.filters.fecha_desde ?? '');
const fechaHasta = ref(props.filters.fecha_hasta ?? '');

let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(applyFilter, 400);
}
function applyFilter() {
 router.get(route('admin.finanzas.pagos.index'), { search: search.value || null, metodo_pago: metodoPago.value === 'all' ? null : metodoPago.value, estado: estadoFilter.value === 'all' ? null : estadoFilter.value, fecha_desde: fechaDesde.value || null, fecha_hasta: fechaHasta.value || null }, { preserveState: true, preserveScroll: true });
}
const hasFilters = computed(() => search.value || (metodoPago.value && metodoPago.value !== 'all') || (estadoFilter.value && estadoFilter.value !== 'all') || fechaDesde.value || fechaHasta.value);
function clearFilters() {
 search.value = ''; metodoPago.value = 'all'; estadoFilter.value = 'all'; fechaDesde.value = ''; fechaHasta.value = ''; router.get(route('admin.finanzas.pagos.index'), {}, { preserveState: true, replace: true });
}

async function confirmDelete(p: any) {
 const ok = await confirm({ title: 'Eliminar Pago', text: '¿Estás seguro?', danger: true, confirmText: 'Sí, eliminar' });

 if (ok) {
router.delete(route('admin.finanzas.pagos.destroy', p.id), { preserveScroll: true, onSuccess: () => success('Pago eliminado') });
}
}

const metodoPagoLabels: Record<string, string> = { efectivo: 'Efectivo', transferencia: 'Transferencia', tarjeta: 'Tarjeta', cheque: 'Cheque', deposito: 'Depósito', otro: 'Otro' };
const estadoColors: Record<string, string> = { completado: 'bg-green-100 text-green-700', pendiente: 'bg-yellow-100 text-yellow-700', rechazado: 'bg-red-100 text-red-700' };
function formatDate(d: string) {
 return d ? new Date(d).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' }) : '';
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Pagos" :description="`${pagos.total} pagos registrados`" :icon="CreditCard">
            <template #actions>
                <Link :href="route('admin.finanzas.pagos.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10"><Plus class="mr-2 h-4 w-4" /> Nuevo Pago</Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[180px] max-w-xs">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar..." class="h-10 rounded-lg border-gray-300 bg-white pl-9 text-sm" @input="onSearch" />
            </div>
            <Select v-model="metodoPago" @update:model-value="applyFilter">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-36"><SelectValue placeholder="Método" /></SelectTrigger>
                <SelectContent><SelectItem value="all">Todos</SelectItem><SelectItem v-for="(l, k) in metodoPagoLabels" :key="k" :value="k">{{ l }}</SelectItem></SelectContent>
            </Select>
            <Select v-model="estadoFilter" @update:model-value="applyFilter">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                <SelectContent><SelectItem value="all">Todos</SelectItem><SelectItem value="completado">Completado</SelectItem><SelectItem value="pendiente">Pendiente</SelectItem><SelectItem value="rechazado">Rechazado</SelectItem></SelectContent>
            </Select>
            <Input v-model="fechaDesde" type="date" class="h-10 w-[140px] rounded-lg border-gray-300 bg-white" @change="applyFilter" />
            <Input v-model="fechaHasta" type="date" class="h-10 w-[140px] rounded-lg border-gray-300 bg-white" @change="applyFilter" />
            <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">#</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Cotización</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Cliente</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Monto</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Método</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Fecha</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="p in pagos.data" :key="p.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell class="font-medium text-gray-900">#{{ p.id }}</TableCell>
                            <TableCell class="text-gray-500 font-mono text-xs">{{ p.cotizacion?.codigo ?? '—' }}</TableCell>
                            <TableCell class="text-gray-500">{{ p.cotizacion?.cliente?.nombre ?? '—' }}</TableCell>
                            <TableCell class="font-medium text-gray-900">{{ format(p.monto) }}</TableCell>
                            <TableCell class="text-gray-500">{{ metodoPagoLabels[p.metodo_pago] || p.metodo_pago }}</TableCell>
                            <TableCell>
                                <Badge class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium" :class="estadoColors[p.estado] || 'bg-gray-100 text-gray-500'">{{ p.estado }}</Badge>
                            </TableCell>
                            <TableCell class="text-gray-500 text-xs">{{ formatDate(p.fecha_pago) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.finanzas.pagos.show', p.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Eye class="h-4 w-4" /></Button>
                                    </Link>
                                    <Link :href="route('admin.finanzas.pagos.edit', p.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(p)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!pagos.data.length">
                            <TableCell colspan="8" class="py-16 text-center text-gray-500">
                                <CreditCard class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay pagos</p>
                                <p class="text-sm text-gray-500 mt-1">Registra el primer pago.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <Pagination :pagination="pagos" />
        </div>
    </div>
</template>
