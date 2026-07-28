<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Shield, Pencil, Trash2, Plus, Search, X, Mail, BadgeCheck, ShieldCheck } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PageTransition, StaggerTransition, ModuleHeader, EmptyState, SkeletonCard, SlideOver } from '@/components/shared';
import type { PaginatedData, User } from '@/types';
import { useConfirm } from '@/composables/useConfirm';

type Props = { users: PaginatedData<User>; filters: { search: string | null }; };
const props = defineProps<Props>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');
const loading = ref(false);
const selectedUser = ref<User | null>(null);
const showSlideOver = ref(false);

router.on('start', () => { loading.value = true; });
router.on('finish', () => { loading.value = false; });

let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.seguridad.users.index'), { search: search.value || null }, { preserveState: true, replace: true }), 400); }

async function confirmDelete(u: User) {
    const ok = await confirm({ title: 'Eliminar Usuario', text: `¿Eliminar a "${u.name}"?`, danger: true, confirmText: 'Sí, eliminar' });
    if (ok) router.delete(route('admin.seguridad.users.destroy', u.id), { preserveScroll: true, onSuccess: () => success('Usuario eliminado') });
}
function showQuickView(u: User) { selectedUser.value = u; showSlideOver.value = true; }
</script>

<template>
    <Head title="Usuarios" />
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Usuarios" :description="`${users.total} usuarios en el sistema`" :icon="Users" gradient="from-cyan-500 to-blue-600">
                <template #actions>
                    <Link :href="route('admin.seguridad.users.create')">
                        <Button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl h-10 transition-all hover:shadow-xl active:scale-95"><Plus class="mr-2 h-4 w-4" /> Nuevo Usuario</Button>
                    </Link>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar por nombre o email..." class="h-10 rounded-xl border-input bg-background pl-10 text-sm" @input="onSearch" />
                </div>
            </div>

            <div v-if="loading && !users.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"><SkeletonCard v-for="i in 8" :key="i" /></div>
            <div v-else-if="!users.data.length">
                <EmptyState title="No hay usuarios" description="Crea el primer usuario." :icon="Users" gradient="from-cyan-500/20 to-blue-600/20" :action="{ label: 'Nuevo Usuario', href: route('admin.seguridad.users.create') }" />
            </div>

            <template v-else>
                <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div v-for="user in users.data" :key="user.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5 hover:border-cyan-500/30 cursor-pointer" @click="showQuickView(user)">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 to-blue-600 opacity-80" />
                        <div class="flex flex-col items-center text-center">
                            <div class="relative">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-xl font-bold text-white shadow-lg ring-4 ring-cyan-500/20">{{ user.name.charAt(0).toUpperCase() }}</div>
                                <div v-if="user.email_verified_at" class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-background"><BadgeCheck class="h-3 w-3 text-white" /></div>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-foreground">{{ user.name }}</h3>
                            <p class="mt-0.5 text-xs text-muted-foreground flex items-center gap-1"><Mail class="h-3 w-3 shrink-0" />{{ user.email }}</p>
                            <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                                <Badge v-if="user.roles?.length" class="border-0 bg-gradient-to-r from-cyan-500/20 to-blue-600/20 text-cyan-400 shadow-none px-3 py-1 text-[10px]">{{ user.roles[0].name }}</Badge>
                                <span v-else class="text-[10px] text-muted-foreground bg-muted/50 px-3 py-1 rounded-full">Sin rol</span>
                            </div>
                            <div class="mt-5 flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-y-1 group-hover:translate-y-0" @click.stop>
                                <Link :href="route('admin.seguridad.users.edit', user.id)"><Button variant="secondary" size="sm" class="rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm dark:bg-gray-800/90"><Pencil class="mr-1.5 h-3.5 w-3.5" />Editar</Button></Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm dark:bg-gray-800/90" @click="confirmDelete(user)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                    </div>
                </StaggerTransition>
            </template>

            <SlideOver :open="showSlideOver" title="Detalles del Usuario" @close="showSlideOver = false">
                <div v-if="selectedUser" class="space-y-5">
                    <div class="flex flex-col items-center"><div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-2xl font-bold text-white shadow-lg ring-4 ring-cyan-500/20">{{ selectedUser.name.charAt(0).toUpperCase() }}</div>
                        <h3 class="mt-3 text-lg font-semibold text-foreground">{{ selectedUser.name }}</h3>
                        <p class="text-sm text-muted-foreground">{{ selectedUser.email }}</p></div>
                    <div class="grid gap-4 rounded-xl bg-muted/30 p-4">
                        <div class="flex items-center gap-3"><ShieldCheck class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ selectedUser.roles?.[0]?.name ?? 'Sin rol' }}</span></div>
                        <div class="flex items-center gap-3"><BadgeCheck class="h-4 w-4" :class="selectedUser.email_verified_at ? 'text-emerald-400' : 'text-muted-foreground'" /><span class="text-sm">{{ selectedUser.email_verified_at ? 'Verificado' : 'No verificado' }}</span></div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <Link :href="route('admin.seguridad.users.edit', selectedUser.id)" class="flex-1"><Button variant="outline" class="w-full rounded-xl border-border"><Pencil class="mr-2 h-4 w-4" />Editar</Button></Link>
                        <Button variant="destructive" size="icon" class="rounded-xl" @click="confirmDelete(selectedUser)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </div>
            </SlideOver>
        </div>
    </PageTransition>
</template>
