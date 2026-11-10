<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, User, Search, X, Users } from '@lucide/vue';
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
import type { PaginatedData } from '@/types';

type ClienteItem = { id: number; tipo_persona: string; nombre: string; email: string | null; telefono: string | null; documento: string | null; empresa: string | null; cotizaciones_count: number; consultas_count: number; };
const props = defineProps<{ clientes: PaginatedData<ClienteItem>; filters: { search: string | null; tipo_persona: string | null }; }>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');
const tipoFilter = ref(props.filters.tipo_persona ?? 'all');

let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.clientes.index'), { search: search.value || null, tipo_persona: tipoFilter.value === 'all' ? null : tipoFilter.value }, { preserveState: true, replace: true }), 400);
}
const hasFilters = computed(() => search.value || (tipoFilter.value && tipoFilter.value !== 'all'));
function clearFilters() {
 search.value = ''; tipoFilter.value = 'all'; router.get(route('admin.clientes.index'), {}, { preserveState: true, replace: true });
}

async function confirmDelete(cliente: ClienteItem) {
    const ok = await confirm({ title: 'Eliminar Cliente', text: `¿Estás seguro de eliminar a "${cliente.nombre}"?`, danger: true, confirmText: 'Sí, eliminar' });

    if (ok) {
router.delete(route('admin.clientes.destroy', cliente.id), { onSuccess: () => success('Cliente eliminado', 'Se ha eliminado correctamente.') });
}
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Clientes" :description="`${clientes.total} clientes registrados`" :icon="Users">
            <template #actions>
                <Link :href="route('admin.clientes.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10">
                        <Plus class="mr-2 h-4 w-4" /> Nuevo Cliente
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar por nombre, email o doc..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
            <Select v-model="tipoFilter" @update:model-value="onSearch">
                <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white w-36"><SelectValue placeholder="Tipo" /></SelectTrigger>
                <SelectContent><SelectItem value="all">Todos</SelectItem><SelectItem value="natural">Natural</SelectItem><SelectItem value="juridica">Jurídica</SelectItem></SelectContent>
            </Select>
            <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500 hover:text-gray-700" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Nombre</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Documento</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Tipo</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Email</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Teléfono</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Cotizaciones</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="c in clientes.data" :key="c.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                                        <User class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ c.nombre }}</p>
                                        <p v-if="c.empresa" class="text-xs text-gray-500">{{ c.empresa }}</p>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-gray-500">{{ c.documento ?? '—' }}</TableCell>
                            <TableCell>
                                <Badge variant="secondary" class="rounded-md px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 shadow-none">
                                    {{ c.tipo_persona === 'natural' ? 'Natural' : 'Jurídica' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-gray-500">{{ c.email ?? '—' }}</TableCell>
                            <TableCell class="text-gray-500">{{ c.telefono ?? '—' }}</TableCell>
                            <TableCell class="text-gray-500">{{ c.cotizaciones_count }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.clientes.edit', c.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(c)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!clientes.data.length">
                            <TableCell colspan="7" class="py-16 text-center text-gray-500">
                                <Users class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay clientes</p>
                                <p class="text-sm text-gray-500 mt-1">Registra tu primer cliente para empezar.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <Pagination :pagination="clientes" />
        </div>
    </div>
</template>
