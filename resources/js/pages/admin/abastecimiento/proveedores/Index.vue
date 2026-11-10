<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Building2, Pencil, Plus, Search, Trash2, Users, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ProveedorController from '@/actions/App/Http/Controllers/Admin/Abastecimiento/ProveedorController';
import { ModuleHeader, Pagination } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useConfirm } from '@/composables/useConfirm';
import { index as proveedoresIndex } from '@/routes/admin/abastecimiento/proveedores';
import type { PaginatedData } from '@/types';

type Proveedor = {
    id: number;
    nombre: string;
    documento: string | null;
    contacto: string | null;
    email: string | null;
    telefono: string | null;
    activo: boolean;
    compras_count: number;
};

const props = defineProps<{
    proveedores: PaginatedData<Proveedor>;
    filters: { search: string | null; estado: string | null };
}>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const search = ref(props.filters.search ?? '');
const estado = ref(props.filters.estado ?? 'all');
const { confirm, success } = useConfirm();

let searchTimer: number | undefined;
function applyFilters() {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
        router.get(proveedoresIndex.url(teamSlug.value), {
            search: search.value || null,
            estado: estado.value === 'all' ? null : estado.value,
        }, { preserveState: true, replace: true });
    }, 350);
}

function clearFilters() {
    search.value = '';
    estado.value = 'all';
    router.get(proveedoresIndex.url(teamSlug.value), {}, { preserveState: true, replace: true });
}

async function archiveSupplier(proveedor: Proveedor) {
    const confirmed = await confirm({
        title: 'Archivar proveedor',
        text: `¿Quieres archivar a ${proveedor.nombre}? Sus compras anteriores se conservarán.`,
        danger: true,
        confirmText: 'Archivar',
    });

    if (confirmed) {
        router.delete(ProveedorController.destroy.url({ current_team: teamSlug.value, proveedor: proveedor.id }), {
            onSuccess: () => success('Proveedor archivado'),
        });
    }
}
</script>

<template>
    <Head title="Proveedores" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Proveedores" description="Administra los proveedores de tu negocio" :icon="Building2">
            <template #actions>
                <Link :href="ProveedorController.create.url(teamSlug)">
                    <Button class="h-10 rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700">
                        <Plus class="mr-2 h-4 w-4" /> Nuevo proveedor
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative min-w-[200px] max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <Input v-model="search" placeholder="Buscar nombre, documento o contacto..." class="h-10 rounded-lg border-gray-300 pl-10 text-sm" @input="applyFilters" />
            </div>
            <select v-model="estado" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm" @change="applyFilters">
                <option value="all">Todos</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
            </select>
            <Button v-if="search || estado !== 'all'" variant="ghost" size="sm" class="h-10 rounded-lg text-gray-500" @click="clearFilters">
                <X class="mr-1.5 h-4 w-4" /> Limpiar
            </Button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Proveedor</th>
                            <th class="px-5 py-3">Contacto</th>
                            <th class="px-5 py-3">Compras</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="proveedor in proveedores.data" :key="proveedor.id" class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ proveedor.nombre }}</p>
                                <p class="text-xs text-gray-500">{{ proveedor.documento || 'Sin documento' }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <p>{{ proveedor.contacto || '—' }}</p>
                                <p class="text-xs text-gray-500">{{ proveedor.telefono || proveedor.email || 'Sin datos de contacto' }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ proveedor.compras_count }}</td>
                            <td class="px-5 py-4">
                                <Badge :class="proveedor.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="rounded-md shadow-none">
                                    {{ proveedor.activo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <Link :href="ProveedorController.edit.url({ current_team: teamSlug, proveedor: proveedor.id })">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg p-0" :aria-label="`Editar ${proveedor.nombre}`">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg p-0 text-red-600 hover:border-red-300 hover:text-red-700" :aria-label="`Archivar ${proveedor.nombre}`" @click="archiveSupplier(proveedor)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!proveedores.data.length">
                            <td colspan="5" class="px-5 py-16 text-center text-gray-500">
                                <Users class="mx-auto mb-3 h-10 w-10 text-gray-300" />
                                <p class="font-medium text-gray-900">No hay proveedores registrados</p>
                                <p class="mt-1 text-sm">Agrega un proveedor para empezar a registrar compras.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Pagination :pagination="proveedores" />
    </div>
</template>
