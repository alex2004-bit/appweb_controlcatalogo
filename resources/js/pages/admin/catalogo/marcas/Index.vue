<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Tag, Plus, Pencil, Trash2, Search, X } from '@lucide/vue';
import { ref } from 'vue';
import { ConfirmDeleteDialog, ModuleHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Marca = {
    id: number;
    nombre: string;
    slug: string;
    logo: string | null;
    descripcion: string | null;
    activo: boolean;
    productos_count: number;
    created_at: string;
};

defineProps<{ marcas: Marca[] }>();

const search = ref('');
const estadoFilter = ref('all');

let st: number | undefined;
function onSearch() {
    clearTimeout(st);
    st = window.setTimeout(() => {
        router.get(route('admin.catalogo.marcas.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true });
    }, 400);
}

const deleteId = ref<number | null>(null);
const deleteNombre = ref('');
const showDelete = ref(false);

function confirmDelete(marca: Marca) {
    deleteId.value = marca.id;
    deleteNombre.value = marca.nombre;
    showDelete.value = true;
}

function executeDelete() {
    if (!deleteId.value) {
return;
}

    router.delete(route('admin.catalogo.marcas.destroy', deleteId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDelete.value = false;
            deleteId.value = null;
        },
    });
}
</script>

<template>
    <Head title="Marcas" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Marcas" description="Gestiona las marcas del catálogo" :icon="Tag">
            <template #actions>
                <Link :href="route('admin.catalogo.marcas.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10">
                        <Plus class="mr-2 h-4 w-4" /> Nueva Marca
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar marcas..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
            <Select v-model="estadoFilter" @update:model-value="onSearch">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem><SelectItem value="activo">Activos</SelectItem><SelectItem value="inactivo">Inactivos</SelectItem>
                </SelectContent>
            </Select>
            <Button v-if="search || (estadoFilter && estadoFilter !== 'all')" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="search = ''; estadoFilter = 'all'; router.get(route('admin.catalogo.marcas.index'), {}, { preserveState: true, replace: true })"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Logo</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Nombre</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Slug</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Productos</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Estado</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Creado</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="marca in marcas" :key="marca.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell>
                                <div v-if="marca.logo" class="h-10 w-10 rounded-lg overflow-hidden bg-gray-100">
                                    <img :src="marca.logo" :alt="marca.nombre" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500 font-semibold text-sm">
                                    {{ marca.nombre.charAt(0).toUpperCase() }}
                                </div>
                            </TableCell>
                            <TableCell class="font-medium text-gray-900">{{ marca.nombre }}</TableCell>
                            <TableCell class="text-gray-500 font-mono text-xs">{{ marca.slug }}</TableCell>
                            <TableCell>
                                <Badge variant="secondary" class="rounded-full text-xs shadow-none">{{ marca.productos_count }}</Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :class="marca.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="shadow-none rounded-md px-2.5 py-0.5 text-xs font-medium">
                                    {{ marca.activo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-gray-500 text-xs">{{ new Date(marca.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' }) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.catalogo.marcas.edit', marca.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(marca)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!marcas.length">
                            <TableCell colspan="7" class="py-16 text-center text-gray-500">
                                <Tag class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay marcas registradas</p>
                                <p class="text-sm text-gray-500 mt-1">Crea tu primera marca para comenzar.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <ConfirmDeleteDialog
            :open="showDelete"
            title="Eliminar Marca"
            :description="`¿Estás seguro de eliminar &quot;${deleteNombre}&quot;? Esta acción no se puede deshacer.`"
            @confirm="executeDelete"
            @update:open="showDelete = false"
        />
    </div>
</template>
