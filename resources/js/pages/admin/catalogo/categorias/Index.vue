<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { FolderTree, Plus, Pencil, Trash2, Search, Grid3X3, List, X } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { ConfirmDeleteDialog, Pagination, PageTransition, StaggerTransition, ModuleHeader } from '@/components/shared';
import type { PaginatedData } from '@/types';

type Categoria = {
    id: number; nombre: string; slug: string; descripcion: string | null;
    parent: { id: number; nombre: string } | null; activo: boolean; deleted_at: string | null;
};

type Props = { categorias: PaginatedData<Categoria>; filters: { search: string | null; estado: string | null } };
const props = defineProps<Props>();

const viewMode = ref<'grid' | 'list'>('grid');
const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');

let st: number | undefined;
function onSearch() {
    clearTimeout(st);
    st = window.setTimeout(() => {
        router.get(route('admin.catalogo.categorias.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true });
    }, 400);
}
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() {
    search.value = ''; estadoFilter.value = 'all';
    router.get(route('admin.catalogo.categorias.index'), {}, { preserveState: true, replace: true });
}

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) { deleteId.value = id; showDelete.value = true; }
function executeDelete() {
    if (deleteId.value) router.delete(route('admin.catalogo.categorias.destroy', deleteId.value), { preserveScroll: true, onSuccess: () => { showDelete.value = false; deleteId.value = null; } });
}

const colors = ['from-cyan-500 to-teal-600', 'from-blue-500 to-indigo-600', 'from-amber-500 to-orange-600', 'from-purple-500 to-pink-600', 'from-emerald-500 to-teal-600', 'from-rose-500 to-red-600'];
function getColor(i: number) { return colors[i % colors.length]; }
</script>

<template>
    <Head title="Categorías" />
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Categorías" description="Organiza tu catálogo en categorías" :icon="FolderTree" gradient="from-cyan-500 to-teal-600">
                <template #actions>
                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex items-center rounded-xl border border-border bg-card p-1">
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg" :class="viewMode === 'grid' ? 'bg-accent' : ''" @click="viewMode = 'grid'"><Grid3X3 class="h-4 w-4" /></Button>
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg" :class="viewMode === 'list' ? 'bg-accent' : ''" @click="viewMode = 'list'"><List class="h-4 w-4" /></Button>
                        </div>
                        <Link :href="route('admin.catalogo.categorias.create')">
                            <Button class="bg-gradient-to-r from-cyan-500 to-teal-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-600 hover:to-teal-700 rounded-xl h-10">
                                <Plus class="mr-2 h-4 w-4" /> Nueva Categoría
                            </Button>
                        </Link>
                    </div>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar categorías..." class="h-10 rounded-xl border-input bg-card pl-10 text-sm" @input="onSearch" />
                </div>
                <Select v-model="estadoFilter" @update:model-value="onSearch">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-card w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem><SelectItem value="activo">Activos</SelectItem><SelectItem value="inactivo">Inactivos</SelectItem>
                    </SelectContent>
                </Select>
                <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-xl" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <div v-if="viewMode === 'grid'">
                <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div v-for="(cat, i) in categorias.data" :key="cat.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r" :class="getColor(i)" />
                        <div class="flex items-start justify-between">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl shadow-sm" :class="'bg-gradient-to-br ' + getColor(i)">
                                <FolderTree class="h-6 w-6 text-white" />
                            </div>
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('admin.catalogo.categorias.edit', cat.id)">
                                    <Button variant="secondary" size="icon" class="h-8 w-8 rounded-lg bg-white/90 hover:bg-white text-gray-700 shadow-sm"><Pencil class="h-3.5 w-3.5" /></Button>
                                </Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-lg bg-white/90 hover:bg-white text-red-500 shadow-sm" @click="confirmDelete(cat.id)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-foreground">{{ cat.nombre }}</h3>
                        <p v-if="cat.descripcion" class="mt-1 text-xs text-muted-foreground line-clamp-2">{{ cat.descripcion }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span v-if="cat.parent" class="text-xs text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-md">{{ cat.parent.nombre }}</span>
                            <span v-else class="text-xs text-muted-foreground">Principal</span>
                            <Badge :class="cat.activo ? 'bg-emerald-500/15 text-emerald-500 border-emerald-500/20 border' : 'bg-slate-500/15 text-slate-400 border-slate-500/20 border'" class="shadow-none text-[10px]">
                                {{ cat.activo ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </div>
                    </div>
                </StaggerTransition>
                <div v-if="!categorias.data.length" class="flex flex-col items-center justify-center py-20 text-center">
                    <FolderTree class="h-16 w-16 text-muted-foreground/20 mb-4" />
                    <h3 class="text-lg font-semibold text-foreground">No hay categorías</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Crea tu primera categoría para organizar el catálogo.</p>
                </div>
                <Pagination :pagination="categorias" class="mt-4" />
            </div>

            <div v-else class="overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="border-border/50 hover:bg-transparent">
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nombre</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Categoría Padre</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Estado</TableHead>
                                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="cat in categorias.data" :key="cat.id" class="border-border/30 transition-colors hover:bg-accent/50">
                                <TableCell class="font-medium text-foreground">{{ cat.nombre }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ cat.parent?.nombre ?? '—' }}</TableCell>
                                <TableCell><Badge :class="cat.activo ? 'bg-emerald-500/15 text-emerald-500 border-emerald-500/20 border' : 'bg-slate-500/15 text-slate-400 border-slate-500/20 border'" class="shadow-none">{{ cat.activo ? 'Activo' : 'Inactivo' }}</Badge></TableCell>
                                <TableCell class="text-right"><div class="flex justify-end gap-2">
                                    <Link :href="route('admin.catalogo.categorias.edit', cat.id)"><Button variant="outline" size="sm" class="rounded-xl border-border bg-card"><Pencil class="h-4 w-4" /></Button></Link>
                                    <Button variant="destructive" size="sm" class="rounded-xl" @click="confirmDelete(cat.id)"><Trash2 class="h-4 w-4" /></Button>
                                </div></TableCell>
                            </TableRow>
                            <TableRow v-if="!categorias.data.length"><TableCell colspan="4" class="py-12 text-center text-muted-foreground">No hay categorías registradas.</TableCell></TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div class="border-t border-border/50 p-4"><Pagination :pagination="categorias" /></div>
            </div>

            <ConfirmDeleteDialog :open="showDelete" title="Eliminar Categoría" description="¿Estás seguro de eliminar esta categoría?" @confirm="executeDelete" @cancel="showDelete = false" />
        </div>
    </PageTransition>
</template>
