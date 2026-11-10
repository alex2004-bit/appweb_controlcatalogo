<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Trash2, Search, FileText, X } from '@lucide/vue';
import { ref, computed } from 'vue';
import { ConfirmDeleteDialog, ModuleHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useMoney } from '@/composables/useMoney';
import type { PaginatedData } from '@/types';

type CotizacionItem = { id: number; codigo: string; total: string; estado: string; created_at: string; cliente: { id: number; nombre: string } | null; creador: { id: number; name: string }; };
const props = defineProps<{ cotizaciones: PaginatedData<CotizacionItem>; filters: { search: string | null; estado: string | null }; }>();

const { format } = useMoney();

const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');
let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.cotizaciones.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true }), 400);
}
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() {
 search.value = ''; estadoFilter.value = 'all'; router.get(route('admin.cotizaciones.index'), {}, { preserveState: true, replace: true });
}

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) {
 deleteId.value = id; showDelete.value = true;
}
function destroy() {
 if (deleteId.value) {
router.delete(route('admin.cotizaciones.destroy', deleteId.value), { onSuccess: () => {
 showDelete.value = false; deleteId.value = null;
} });
}
}

const estadoColors: Record<string, string> = {
    borrador: 'bg-gray-100 text-gray-500',
    enviada: 'bg-blue-100 text-blue-700',
    aprobada: 'bg-green-100 text-green-700',
    rechazada: 'bg-red-100 text-red-700',
};
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Cotizaciones" description="Presupuestos y cotizaciones para clientes" :icon="FileText">
            <template #actions>
                <Link :href="route('admin.cotizaciones.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10">
                        <Plus class="mr-2 h-4 w-4" /> Nueva Cotización
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar cotizaciones..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
            <Select v-model="estadoFilter" @update:model-value="onSearch">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-36"><SelectValue placeholder="Estado" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todas</SelectItem>
                    <SelectItem value="borrador">Borrador</SelectItem>
                    <SelectItem value="enviada">Enviada</SelectItem>
                    <SelectItem value="aprobada">Aprobada</SelectItem>
                    <SelectItem value="rechazada">Rechazada</SelectItem>
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
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Total</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Fecha</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="c in cotizaciones.data" :key="c.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell class="font-mono font-medium text-gray-900">{{ c.codigo }}</TableCell>
                            <TableCell class="text-gray-500">{{ c.cliente?.nombre ?? 'Sin cliente' }}</TableCell>
                            <TableCell class="font-medium text-blue-600">{{ format(c.total) }}</TableCell>
                            <TableCell>
                                <Badge class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium" :class="estadoColors[c.estado] || 'bg-gray-100 text-gray-500'">
                                    {{ c.estado }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-gray-500 text-xs">{{ c.created_at }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.cotizaciones.show', c.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Eye class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(c.id)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!cotizaciones.data.length">
                            <TableCell colspan="6" class="py-16 text-center text-gray-500">
                                <FileText class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay cotizaciones</p>
                                <p class="text-sm text-gray-500 mt-1">Crea tu primera cotización.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <ConfirmDeleteDialog :open="showDelete" title="Eliminar cotización" description="¿Estás seguro? Esta acción no se puede deshacer." @confirm="destroy" @cancel="showDelete = false" />
    </div>
</template>
