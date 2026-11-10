<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Trash2, Search, ShoppingCart, X } from '@lucide/vue';
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

type PedidoItem = { id: number; codigo: string; total: string; estado: string; pagado: boolean; metodo_pago: string | null; created_at: string; cliente: { id: number; nombre: string } | null; vendedor: { id: number; name: string } | null; creador: { id: number; name: string }; };
const props = defineProps<{ pedidos: PaginatedData<PedidoItem>; filters: { search: string | null; estado: string | null }; }>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');

let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.pedidos.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true }), 400);
}
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() {
 search.value = ''; estadoFilter.value = 'all'; router.get(route('admin.pedidos.index'), {}, { preserveState: true, replace: true });
}

async function confirmDelete(pedido: PedidoItem) {
    const ok = await confirm({ title: 'Eliminar Pedido', text: `¿Eliminar el pedido ${pedido.codigo}?`, danger: true, confirmText: 'Sí, eliminar' });

    if (ok) {
router.delete(route('admin.pedidos.destroy', pedido.id), { onSuccess: () => success('Pedido eliminado') });
}
}

const estadoColor: Record<string, string> = { pendiente: 'bg-yellow-100 text-yellow-700', procesando: 'bg-blue-100 text-blue-700', completado: 'bg-green-100 text-green-700', cancelado: 'bg-red-100 text-red-700' };
const estadoLabels: Record<string, string> = { pendiente: 'Pendiente', procesando: 'Procesando', completado: 'Completado', cancelado: 'Cancelado' };
const metodoPagoLabels: Record<string, string> = { efectivo: 'Efectivo', transferencia: 'Transferencia', tarjeta: 'Tarjeta', cheque: 'Cheque', deposito: 'Depósito', otro: 'Otro' };

const { format } = useMoney();

function formatDate(d: string) {
 return d ? new Date(d).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' }) : '';
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Pedidos" :description="`${pedidos.total} pedidos registrados`" :icon="ShoppingCart">
            <template #actions>
                <Link :href="route('admin.pedidos.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10"><Plus class="mr-2 h-4 w-4" /> Nuevo Pedido</Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar por código o cliente..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
            <Select v-model="estadoFilter" @update:model-value="onSearch">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-36"><SelectValue placeholder="Estado" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem v-for="(l, k) in estadoLabels" :key="k" :value="k">{{ l }}</SelectItem>
                </SelectContent>
            </Select>
            <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Código</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Cliente</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Vendedor</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Total</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Método Pago</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Fecha</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="p in pedidos.data" :key="p.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell class="font-mono font-medium text-gray-900">{{ p.codigo }}</TableCell>
                            <TableCell class="text-gray-500">{{ p.cliente?.nombre ?? '—' }}</TableCell>
                            <TableCell class="text-gray-500">{{ p.vendedor?.name ?? '—' }}</TableCell>
                            <TableCell class="font-medium text-blue-600">{{ format(p.total) }}</TableCell>
                            <TableCell>
                                <Badge class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium" :class="estadoColor[p.estado] || 'bg-gray-100 text-gray-500'">
                                    {{ estadoLabels[p.estado] || p.estado }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-gray-500">{{ metodoPagoLabels[p.metodo_pago ?? ''] || '—' }}</TableCell>
                            <TableCell class="text-gray-500 text-xs">{{ formatDate(p.created_at) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.pedidos.show', p.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Eye class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(p)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!pedidos.data.length">
                            <TableCell colspan="7" class="py-16 text-center text-gray-500">
                                <ShoppingCart class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay pedidos</p>
                                <p class="text-sm text-gray-500 mt-1">Crea tu primer pedido.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <Pagination :pagination="pedidos" />
        </div>
    </div>
</template>
