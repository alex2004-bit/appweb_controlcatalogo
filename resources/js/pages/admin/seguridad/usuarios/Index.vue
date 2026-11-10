<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Pencil, Trash2, Plus, Search, Mail, BadgeCheck } from '@lucide/vue';
import { ref } from 'vue';
import { ModuleHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useConfirm } from '@/composables/useConfirm';
import type { PaginatedData, User } from '@/types';

type Props = { users: PaginatedData<User>; filters: { search: string | null }; };
const props = defineProps<Props>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');

let st: number | undefined;
function onSearch() {
 clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.seguridad.users.index'), { search: search.value || null }, { preserveState: true, replace: true }), 400);
}

async function confirmDelete(u: User) {
    const ok = await confirm({ title: 'Eliminar Usuario', text: `¿Eliminar a "${u.name}"?`, danger: true, confirmText: 'Sí, eliminar' });

    if (ok) {
router.delete(route('admin.seguridad.users.destroy', u.id), { preserveScroll: true, onSuccess: () => success('Usuario eliminado') });
}
}
</script>

<template>
    <Head title="Usuarios" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Usuarios" :description="`${users.total} usuarios en el sistema`" :icon="Users">
            <template #actions>
                <Link :href="route('admin.seguridad.users.create')">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10"><Plus class="mr-2 h-4 w-4" /> Nuevo Usuario</Button>
                </Link>
            </template>
        </ModuleHeader>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <Input v-model="search" placeholder="Buscar por nombre o email..." class="h-10 rounded-lg border-gray-300 bg-white pl-10 text-sm" @input="onSearch" />
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 border-b border-gray-200">
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Usuario</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Email</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Rol</TableHead>
                            <TableHead class="text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Verificado</TableHead>
                            <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 py-3">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ user.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-gray-500">
                                <div class="flex items-center gap-1">
                                    <Mail class="h-3.5 w-3.5" />
                                    <span>{{ user.email }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge v-if="(user.roles as Array<{ name: string }> | undefined)?.length" class="bg-blue-100 text-blue-700 rounded-md px-2.5 py-0.5 text-xs font-medium shadow-none">
                                    {{ (user.roles as Array<{ name: string }>)[0].name }}
                                </Badge>
                                <span v-else class="text-xs text-gray-500">Sin rol</span>
                            </TableCell>
                            <TableCell>
                                <BadgeCheck v-if="user.email_verified_at" class="h-4 w-4 text-green-500" />
                                <span v-else class="text-xs text-gray-400">No</span>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.seguridad.users.edit', user.id)">
                                        <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-blue-600 hover:border-blue-300"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-gray-200 p-0 text-gray-600 hover:text-red-600 hover:border-red-300" @click="confirmDelete(user)"><Trash2 class="h-4 w-4" /></Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!users.data.length">
                            <TableCell colspan="5" class="py-16 text-center text-gray-500">
                                <Users class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="font-medium text-gray-900">No hay usuarios</p>
                                <p class="text-sm text-gray-500 mt-1">Crea el primer usuario.</p>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
