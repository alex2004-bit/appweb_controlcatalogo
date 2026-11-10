<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { FolderTree, Plus, Pencil, Trash2, Search, X } from '@lucide/vue';
import { ref, computed } from 'vue';
import { ConfirmDeleteDialog, Pagination, ModuleHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types';

type Categoria = {
    id: number; nombre: string; slug: string; descripcion: string | null;
    parent: { id: number; nombre: string } | null; activo: boolean; deleted_at: string | null;
};

type Props = { categorias: PaginatedData<Categoria>; filters: { search: string | null; estado: string | null } };
const { filters } = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
const teamParams = () => ({ current_team: currentTeam?.slug ?? '' });

const search = ref(filters.search ?? '');
const estadoFilter = ref(filters.estado ?? 'all');

let st: number | undefined;
function onSearch() {
    clearTimeout(st);
    st = window.setTimeout(() => {
        router.get(route('admin.catalogo.categorias.index', teamParams()), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true });
    }, 400);
}
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() {
    search.value = ''; estadoFilter.value = 'all';
    router.get(route('admin.catalogo.categorias.index', teamParams()), {}, { preserveState: true, replace: true });
}

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) {
 deleteId.value = id; showDelete.value = true;
}
function executeDelete() {
    if (deleteId.value) {
router.delete(route('admin.catalogo.categorias.destroy', { ...teamParams(), categoria: deleteId.value }), { preserveScroll: true, onSuccess: () => {
 showDelete.value = false; deleteId.value = null;
} });
}
}
</script>

<template>
    <Head title="Categorías" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Categorías" description="Organiza tu catálogo en categorías" :icon="FolderTree">
            <template #actions>
                <Link :href="route('admin.catalogo.categorias.create', teamParams())">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10">
                        <Plus class="mr-2 h-4 w-4" /> Nueva Categoría
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar categorías..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
            <Select v-model="estadoFilter" @update:model-value="onSearch">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem><SelectItem value="activo">Activos</SelectItem><SelectItem value="inactivo">Inactivos</SelectItem>
                </SelectContent>
            </Select>
            <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Nombre</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Categoría Padre</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="cat in categorias.data" :key="cat.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell class="font-medium text-gray-900">{{ cat.nombre }}</TableCell>
                            <TableCell class="text-sm text-gray-500">{{ cat.parent?.nombre ?? '—' }}</TableCell>
                            <TableCell>
                                <Badge :class="cat.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium">
                                    {{ cat.activo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.catalogo.categorias.edit', { ...teamParams(), categoria: cat.id })">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(cat.id)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!categorias.data.length">
                            <TableCell colspan="4" class="py-16 text-center text-gray-500">
                                <FolderTree class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay categorías</p>
                                <p class="text-sm text-gray-500 mt-1">Crea tu primera categoría.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <Pagination :pagination="categorias" />
        </div>

        <ConfirmDeleteDialog :open="showDelete" title="Eliminar Categoría" description="¿Estás seguro de eliminar esta categoría?" @confirm="executeDelete" @cancel="showDelete = false" />
    </div>
</template>
