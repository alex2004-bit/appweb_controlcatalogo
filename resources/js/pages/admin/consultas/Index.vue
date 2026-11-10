<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { MessageSquare, Search, Eye, Trash2, X } from '@lucide/vue';
import { ref, computed } from 'vue';
import { ConfirmDeleteDialog, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types';

type Consulta = { id: number; nombre: string; email: string; mensaje: string; tipo: string; estado: string; leido: boolean; created_at: string; };
const props = defineProps<{ consultas: PaginatedData<Consulta>; filters: { search: string | null; estado: string | null }; }>();

const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');
let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.consultas.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true }), 400);
}
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() {
 search.value = ''; estadoFilter.value = 'all'; router.get(route('admin.consultas.index'), {}, { preserveState: true, replace: true });
}

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) {
 deleteId.value = id; showDelete.value = true;
}
function destroy() {
 if (deleteId.value) {
router.delete(route('admin.consultas.destroy', deleteId.value), { onSuccess: () => {
 showDelete.value = false; deleteId.value = null;
} });
}
}

const estadoColors: Record<string, string> = {
    pendiente: 'bg-yellow-100 text-yellow-700',
    en_proceso: 'bg-blue-100 text-blue-700',
    respondido: 'bg-green-100 text-green-700',
    cerrado: 'bg-gray-100 text-gray-500',
};
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Consultas" description="Consultas y mensajes de clientes" :icon="MessageSquare">
            <template #actions>
                <div class="flex items-center gap-2">
                    <Select v-model="estadoFilter" @update:model-value="onSearch">
                        <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-36"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent><SelectItem value="all">Todas</SelectItem><SelectItem value="pendiente">Pendiente</SelectItem><SelectItem value="en_proceso">En Proceso</SelectItem><SelectItem value="respondido">Respondido</SelectItem><SelectItem value="cerrado">Cerrado</SelectItem></SelectContent>
                    </Select>
                </div>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar por nombre o email..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
            <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Nombre</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Email</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Mensaje</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Fecha</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="c in consultas.data" :key="c.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50" :class="{ 'bg-blue-50/30': !c.leido }">
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <div v-if="!c.leido" class="h-2 w-2 rounded-full bg-blue-500" />
                                    <span class="font-medium text-gray-900">{{ c.nombre }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-gray-500">{{ c.email }}</TableCell>
                            <TableCell class="text-gray-500 max-w-xs truncate">{{ c.mensaje }}</TableCell>
                            <TableCell>
                                <span class="rounded-md px-2.5 py-0.5 text-xs font-medium shadow-none" :class="estadoColors[c.estado] || 'bg-gray-100 text-gray-500'">
                                    {{ c.estado }}
                                </span>
                            </TableCell>
                            <TableCell class="text-gray-500 text-xs">{{ c.created_at }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.consultas.show', c.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Eye class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(c.id)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!consultas.data.length">
                            <TableCell colspan="6" class="py-16 text-center text-gray-500">
                                <MessageSquare class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay consultas</p>
                                <p class="text-sm text-gray-500 mt-1">Las consultas de clientes aparecerán aquí.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <ConfirmDeleteDialog :open="showDelete" title="Eliminar consulta" description="¿Estás seguro? Esta acción no se puede deshacer." @confirm="destroy" @cancel="showDelete = false" />
    </div>
</template>
