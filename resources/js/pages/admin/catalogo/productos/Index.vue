<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Package, Plus, Pencil, Trash2, Search, X } from '@lucide/vue';
import { ref, computed } from 'vue';
import {
    Pagination,
    ModuleHeader,
} from '@/components/shared';
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useConfirm } from '@/composables/useConfirm';
import { useMoney } from '@/composables/useMoney';
import type { PaginatedData } from '@/types';

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

const { filters } = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
const teamParams = () => ({ current_team: currentTeam?.slug ?? '' });
const { confirm } = useConfirm();
const { format } = useMoney();

const search = ref(filters.search ?? '');
const catFilter = ref(filters.categoria_id ?? 'all');
const estadoFilter = ref(filters.estado ?? 'all');
const loading = ref(false);

router.on('start', () => {
 loading.value = true;
});
router.on('finish', () => {
 loading.value = false;
});

let searchTimeout: number | undefined;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(applyAllFilters, 400);
}

function applyAllFilters() {
    router.get(route('admin.catalogo.productos.index', teamParams()), {
        search: search.value || null,
        categoria_id: catFilter.value === 'all' ? null : catFilter.value,
        estado: estadoFilter.value === 'all' ? null : estadoFilter.value,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value = '';
    catFilter.value = 'all';
    estadoFilter.value = 'all';
    router.get(route('admin.catalogo.productos.index', teamParams()), {}, { preserveState: true, replace: true });
}

const hasActiveFilters = computed(() => search.value || (catFilter.value && catFilter.value !== 'all') || (estadoFilter.value && estadoFilter.value !== 'all'));

async function confirmDelete(producto: Producto) {
    const confirmed = await confirm({
        title: 'Eliminar Producto',
        text: `¿Estás seguro de eliminar "${producto.nombre}"?`,
        danger: true,
        confirmText: 'Sí, eliminar',
    });

    if (confirmed) {
        router.delete(route('admin.catalogo.productos.destroy', { ...teamParams(), producto: producto.id }), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Productos" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Productos" :description="`${productos.total} productos en catálogo`" :icon="Package">
            <template #actions>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.catalogo.productos.create', teamParams())">
                        <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10">
                            <Plus class="mr-2 h-4 w-4" /> Nuevo Producto
                        </Button>
                    </Link>
                </div>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar productos..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearchInput" />
            </div>
            <Select v-model="catFilter" @update:model-value="applyAllFilters">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-40"><SelectValue placeholder="Categoría" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todas</SelectItem>
                    <SelectItem v-for="cat in categorias" :key="cat.id" :value="String(cat.id)">{{ cat.nombre }}</SelectItem>
                </SelectContent>
            </Select>
            <Select v-model="estadoFilter" @update:model-value="applyAllFilters">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem value="activo">Activos</SelectItem>
                    <SelectItem value="inactivo">Inactivos</SelectItem>
                </SelectContent>
            </Select>
            <Button v-if="hasActiveFilters" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Producto</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">SKU</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Categoría</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Precio</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="producto in productos.data" :key="producto.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                        <img v-if="producto.imagen" :src="producto.imagen" :alt="producto.nombre" class="h-full w-full object-cover" />
                                        <Package v-else class="h-5 w-5 text-gray-400 m-auto mt-2.5" />
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ producto.nombre }}</span>
                                        <p v-if="producto.descripcion" class="text-xs text-gray-500 line-clamp-1">{{ producto.descripcion }}</p>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span v-if="producto.sku" class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">{{ producto.sku }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </TableCell>
                            <TableCell class="text-sm text-gray-600">{{ producto.categoria?.nombre ?? '—' }}</TableCell>
                            <TableCell class="text-sm font-semibold text-gray-900">{{ producto.precio ? format(producto.precio) : '—' }}</TableCell>
                            <TableCell>
                                <Badge :class="producto.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium">
                                    {{ producto.activo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.catalogo.productos.edit', { ...teamParams(), producto: producto.id })">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(producto)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!productos.data.length">
                            <TableCell colspan="6" class="py-16 text-center text-gray-500">
                                <Package class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay productos</p>
                                <p class="text-sm text-gray-500 mt-1">Agrega tu primer producto para comenzar.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <Pagination :pagination="productos" />
        </div>
    </div>
</template>
