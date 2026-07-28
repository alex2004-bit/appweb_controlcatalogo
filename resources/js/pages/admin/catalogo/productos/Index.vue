<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Package, Plus, Pencil, Trash2, Search, Grid3X3, List, X, Tag, Store, Layers, Eye, MoreHorizontal } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    SlideOver, Pagination, PageTransition, StaggerTransition, ModuleHeader, EmptyState, SkeletonCard,
} from '@/components/shared';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types';
import { useConfirm } from '@/composables/useConfirm';

type Producto = {
    id: number;
    nombre: string;
    slug: string;
    sku: string | null;
    precio: string | null;
    descripcion: string | null;
    imagen: string | null;
    categoria: { id: number; nombre: string } | null;
    activo: boolean;
};

type Props = {
    productos: PaginatedData<Producto>;
    categorias: { id: number; nombre: string }[];
    filters: { search: string | null; categoria_id: string | null; estado: string | null };
};

const props = defineProps<Props>();
const { confirm } = useConfirm();
const page = usePage();

const viewMode = ref<'grid' | 'list'>('grid');
const search = ref(props.filters.search ?? '');
const catFilter = ref(props.filters.categoria_id ?? 'all');
const estadoFilter = ref(props.filters.estado ?? 'all');
const loading = ref(false);
const selectedProducto = ref<Producto | null>(null);
const showSlideOver = ref(false);

router.on('start', () => { loading.value = true; });
router.on('finish', () => { loading.value = false; });

const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

let searchTimeout: number | undefined;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(applyAllFilters, 400);
}

function applyAllFilters() {
    loading.value = true;
    router.get(route('admin.catalogo.productos.index'), {
        search: search.value || null,
        categoria_id: catFilter.value === 'all' ? null : catFilter.value,
        estado: estadoFilter.value === 'all' ? null : estadoFilter.value,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value = '';
    catFilter.value = 'all';
    estadoFilter.value = 'all';
    router.get(route('admin.catalogo.productos.index'), {}, { preserveState: true, replace: true });
}

const hasActiveFilters = computed(() => search.value || (catFilter.value && catFilter.value !== 'all') || (estadoFilter.value && estadoFilter.value !== 'all'));

async function confirmDelete(producto: Producto) {
    const confirmed = await confirm({
        title: 'Eliminar Producto',
        text: `¿Estás seguro de eliminar "${producto.nombre}"? Esta acción no se puede deshacer.`,
        danger: true,
        confirmText: 'Sí, eliminar',
    });
    if (confirmed) {
        router.delete(route('admin.catalogo.productos.destroy', producto.id), {
            preserveScroll: true,
            onSuccess: () => {
                const { success } = useConfirm();
                success('Producto eliminado', 'El producto se ha eliminado correctamente.');
            },
        });
    }
}

function showQuickView(producto: Producto) {
    selectedProducto.value = producto;
    showSlideOver.value = true;
}
</script>

<template>
    <Head title="Productos - Tienda" />
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Tienda Digital" :description="`${productos.total} productos en catálogo`" :icon="Store" gradient="from-blue-500 to-indigo-600">
                <template #actions>
                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex items-center rounded-xl border border-border bg-card p-1 shadow-sm">
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="viewMode === 'grid' ? 'bg-accent shadow-sm' : 'text-muted-foreground'" @click="viewMode = 'grid'"><Grid3X3 class="h-4 w-4" /></Button>
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="viewMode === 'list' ? 'bg-accent shadow-sm' : 'text-muted-foreground'" @click="viewMode = 'list'"><List class="h-4 w-4" /></Button>
                        </div>
                        <Link :href="route('admin.catalogo.productos.create')">
                            <Button class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/25 hover:from-blue-600 hover:to-indigo-700 rounded-xl h-10 transition-all hover:shadow-xl hover:shadow-blue-500/30 active:scale-95">
                                <Plus class="mr-2 h-4 w-4" /> Nuevo Producto
                            </Button>
                        </Link>
                    </div>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar productos..." class="h-10 rounded-xl border-input bg-background pl-10 text-sm" @input="onSearchInput" />
                </div>
                <Select v-model="catFilter" @update:model-value="applyAllFilters">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-background w-40"><SelectValue placeholder="Categoría" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas</SelectItem>
                        <SelectItem v-for="cat in categorias" :key="cat.id" :value="String(cat.id)">{{ cat.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="estadoFilter" @update:model-value="applyAllFilters">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-background w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem value="activo">Activos</SelectItem>
                        <SelectItem value="inactivo">Inactivos</SelectItem>
                    </SelectContent>
                </Select>
                <Button v-if="hasActiveFilters" variant="ghost" size="sm" class="h-10 rounded-xl text-muted-foreground hover:text-foreground" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <div v-if="loading && !productos.data.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <SkeletonCard v-for="i in 8" :key="i" />
            </div>

            <div v-else-if="!productos.data.length">
                <EmptyState
                    title="Tu tienda está vacía"
                    description="Agrega tu primer producto para comenzar a vender."
                    :icon="Package"
                    gradient="from-blue-500/20 to-indigo-600/20"
                    :action="{ label: 'Nuevo Producto', href: route('admin.catalogo.productos.create') }"
                />
            </div>

            <template v-else>
                <div v-if="viewMode === 'grid'">
                    <StaggerTransition tag="div" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div v-for="producto in productos.data" :key="producto.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5 hover:border-blue-500/30 cursor-pointer" @click="showQuickView(producto)">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-muted to-muted/50">
                                <img v-if="producto.imagen" :src="producto.imagen" :alt="producto.nombre" class="h-full w-full object-cover transition-all duration-500 group-hover:scale-110" />
                                <div v-else class="flex h-full items-center justify-center">
                                    <Package class="h-12 w-12 text-muted-foreground/30" />
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                                <div class="absolute top-3 right-3 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-all duration-300 -translate-y-2 group-hover:translate-y-0" @click.stop>
                                    <Link :href="route('admin.catalogo.productos.edit', producto.id)">
                                        <Button variant="secondary" size="icon" class="h-9 w-9 rounded-xl bg-black/60 hover:bg-black/80 text-white border-0 shadow-lg backdrop-blur-sm"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="secondary" size="icon" class="h-9 w-9 rounded-xl bg-black/60 hover:bg-black/80 text-red-400 border-0 shadow-lg backdrop-blur-sm" @click="confirmDelete(producto)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                                <div class="absolute bottom-3 left-3 flex gap-1.5">
                                    <Badge :class="producto.activo ? 'bg-emerald-500/90 text-white border-0 shadow-sm' : 'bg-slate-500/80 text-white border-0 shadow-sm'" class="text-[10px] font-semibold px-2.5 py-1 backdrop-blur-sm">{{ producto.activo ? 'Activo' : 'Inactivo' }}</Badge>
                                    <Badge v-if="producto.categoria" variant="secondary" class="text-[10px] px-2.5 py-1 bg-black/40 text-white border-0 backdrop-blur-sm">{{ producto.categoria.nombre }}</Badge>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-foreground truncate group-hover:text-blue-400 transition-colors">{{ producto.nombre }}</p>
                                        <p v-if="producto.descripcion" class="text-xs text-muted-foreground mt-0.5 line-clamp-1">{{ producto.descripcion }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <p class="text-base font-bold text-blue-400">{{ producto.precio ? '$' + producto.precio : '—' }}</p>
                                    <div v-if="producto.sku" class="flex items-center gap-1 rounded-md bg-muted/50 px-2 py-1">
                                        <Tag class="h-3 w-3 text-muted-foreground" />
                                        <span class="font-mono text-[10px] text-muted-foreground">{{ producto.sku }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </StaggerTransition>
                    <Pagination :pagination="productos" class="mt-4" />
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="border-border/50 hover:bg-transparent">
                                    <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Producto</TableHead>
                                    <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">SKU</TableHead>
                                    <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Categoría</TableHead>
                                    <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Precio</TableHead>
                                    <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Estado</TableHead>
                                    <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="producto in productos.data" :key="producto.id" class="border-border/30 transition-colors hover:bg-accent/30 cursor-pointer" @click="showQuickView(producto)">
                                    <TableCell class="font-medium">
                                        <div class="flex items-center gap-3">
                                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-muted to-muted/50 shadow-sm">
                                                <img v-if="producto.imagen" :src="producto.imagen" :alt="producto.nombre" class="h-full w-full object-cover" />
                                                <Package v-else class="h-5 w-5 text-muted-foreground/40 m-auto" />
                                            </div>
                                            <div>
                                                <span class="text-foreground font-medium">{{ producto.nombre }}</span>
                                                <p v-if="producto.descripcion" class="text-xs text-muted-foreground line-clamp-1">{{ producto.descripcion }}</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell><span class="font-mono text-xs text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-md">{{ producto.sku ?? '—' }}</span></TableCell>
                                    <TableCell class="text-muted-foreground">{{ producto.categoria?.nombre ?? '—' }}</TableCell>
                                    <TableCell class="font-semibold text-blue-400">{{ producto.precio ? '$' + producto.precio : '—' }}</TableCell>
                                    <TableCell>
                                        <Badge :class="producto.activo ? 'bg-emerald-500/15 text-emerald-500 border-emerald-500/20 border' : 'bg-slate-500/15 text-slate-400 border-slate-500/20 border'" class="shadow-none">{{ producto.activo ? 'Activo' : 'Inactivo' }}</Badge>
                                    </TableCell>
                                    <TableCell class="text-right" @click.stop>
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.catalogo.productos.edit', producto.id)">
                                                <Button variant="outline" size="sm" class="rounded-xl border-border h-9 w-9 p-0"><Pencil class="h-4 w-4" /></Button>
                                            </Link>
                                            <Button variant="destructive" size="sm" class="rounded-xl h-9 w-9 p-0" @click="confirmDelete(producto)"><Trash2 class="h-4 w-4" /></Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="border-t border-border/50 p-4"><Pagination :pagination="productos" /></div>
                </div>
            </template>

            <SlideOver :open="showSlideOver" title="Vista rápida del producto" @close="showSlideOver = false">
                <div v-if="selectedProducto" class="space-y-5">
                    <div class="aspect-[4/3] rounded-xl overflow-hidden bg-muted">
                        <img v-if="selectedProducto.imagen" :src="selectedProducto.imagen" :alt="selectedProducto.nombre" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center"><Package class="h-16 w-16 text-muted-foreground/30" /></div>
                    </div>
                    <div><h3 class="text-lg font-semibold text-foreground">{{ selectedProducto.nombre }}</h3>
                    <p class="text-sm text-muted-foreground mt-1">{{ selectedProducto.descripcion ?? 'Sin descripción' }}</p></div>
                    <div class="grid grid-cols-2 gap-4 rounded-xl bg-muted/30 p-4">
                        <div><p class="text-xs text-muted-foreground">Precio</p><p class="text-lg font-bold text-blue-400">${{ selectedProducto.precio ?? '0.00' }}</p></div>
                        <div><p class="text-xs text-muted-foreground">SKU</p><p class="text-sm font-mono">{{ selectedProducto.sku ?? '—' }}</p></div>
                        <div><p class="text-xs text-muted-foreground">Categoría</p><p class="text-sm">{{ selectedProducto.categoria?.nombre ?? '—' }}</p></div>
                        <div><p class="text-xs text-muted-foreground">Estado</p><Badge :class="selectedProducto.activo ? 'bg-emerald-500/15 text-emerald-500' : 'bg-slate-500/15 text-slate-400'" class="mt-0.5">{{ selectedProducto.activo ? 'Activo' : 'Inactivo' }}</Badge></div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <Link :href="route('admin.catalogo.productos.edit', selectedProducto.id)" class="flex-1">
                            <Button variant="outline" class="w-full rounded-xl border-border"><Pencil class="mr-2 h-4 w-4" />Editar</Button>
                        </Link>
                        <Button variant="destructive" class="rounded-xl" @click="confirmDelete(selectedProducto)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </div>
            </SlideOver>
        </div>
    </PageTransition>
</template>
