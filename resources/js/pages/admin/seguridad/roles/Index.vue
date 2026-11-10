<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShieldCheck, Shield, Pencil, Trash2, Plus, Search, Users, Key } from '@lucide/vue';
import { ref } from 'vue';
import { ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useConfirm } from '@/composables/useConfirm';
import type { PaginatedData } from '@/types';

type Role = { id: number; name: string; permissions_count?: number; permissions: { name: string }[]; users_count?: number; };
type Props = { roles: PaginatedData<Role>; filters: { search: string | null }; };
const props = defineProps<Props>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');

let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.seguridad.roles.index'), { search: search.value || null }, { preserveState: true, replace: true }), 400);
}

async function confirmDelete(r: Role) {
 const ok = await confirm({ title: 'Eliminar Rol', text: `¿Eliminar el rol "${r.name}"?`, danger: true, confirmText: 'Sí, eliminar' });

 if (ok) {
router.delete(route('admin.seguridad.roles.destroy', r.id), { preserveScroll: true, onSuccess: () => success('Rol eliminado') });
}
}
</script>

<template>
    <Head title="Roles" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Roles" :description="`${roles.total} roles configurados`" :icon="ShieldCheck">
            <template #actions>
                <Link :href="route('admin.seguridad.roles.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10"><Plus class="mr-2 h-4 w-4" /> Nuevo Rol</Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar roles..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Rol</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Permisos</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Usuarios</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="role in roles.data" :key="role.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                        <Shield class="h-5 w-5" />
                                    </div>
                                    <span class="font-medium text-gray-900">{{ role.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1 text-sm text-gray-500">
                                    <Key class="h-3.5 w-3.5" />
                                    <span>{{ role.permissions?.length ?? 0 }} permiso(s)</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1 text-sm text-gray-500">
                                    <Users class="h-3.5 w-3.5" />
                                    <span>{{ role.users_count ?? 0 }} usuario(s)</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div v-if="role.name !== 'Super Admin'" class="flex justify-end gap-2">
                                    <Link :href="route('admin.seguridad.roles.edit', role.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(role)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!roles.data.length">
                            <TableCell colspan="4" class="py-16 text-center text-gray-500">
                                <ShieldCheck class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay roles</p>
                                <p class="text-sm text-gray-500 mt-1">Crea el primer rol.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
