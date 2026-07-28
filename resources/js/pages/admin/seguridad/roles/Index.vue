<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Shield, ShieldCheck, Pencil, Trash2, Plus, Search, X, Users, Key } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PageTransition, StaggerTransition, ModuleHeader, EmptyState, SkeletonCard, SlideOver } from '@/components/shared';
import type { PaginatedData } from '@/types';
import { useConfirm } from '@/composables/useConfirm';

type Role = { id: number; name: string; permissions_count?: number; permissions: { name: string }[]; users_count?: number; };
type Props = { roles: PaginatedData<Role>; filters: { search: string | null }; };
const props = defineProps<Props>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');
const loading = ref(false);
const selectedRole = ref<Role | null>(null);
const showSlideOver = ref(false);

router.on('start', () => { loading.value = true; });
router.on('finish', () => { loading.value = false; });

let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.seguridad.roles.index'), { search: search.value || null }, { preserveState: true, replace: true }), 400); }

async function confirmDelete(r: Role) { const ok = await confirm({ title: 'Eliminar Rol', text: `¿Eliminar el rol "${r.name}"?`, danger: true, confirmText: 'Sí, eliminar' }); if (ok) router.delete(route('admin.seguridad.roles.destroy', r.id), { preserveScroll: true, onSuccess: () => success('Rol eliminado') }); }
function showQuickView(r: Role) { selectedRole.value = r; showSlideOver.value = true; }

const colors = ['from-cyan-500 to-blue-600', 'from-purple-500 to-pink-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-red-600'];
const lightColors = ['from-cyan-500/10', 'from-purple-500/10', 'from-emerald-500/10', 'from-amber-500/10', 'from-rose-500/10'];
function getColor(i: number) { return colors[i % colors.length]; }
function getLight(i: number) { return lightColors[i % lightColors.length]; }
</script>

<template>
    <Head title="Roles" />
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Roles" :description="`${roles.total} roles configurados`" :icon="ShieldCheck" gradient="from-purple-500 to-pink-600">
                <template #actions>
                    <Link :href="route('admin.seguridad.roles.create')">
                        <Button class="bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/25 rounded-xl h-10 transition-all hover:shadow-xl active:scale-95"><Plus class="mr-2 h-4 w-4" /> Nuevo Rol</Button>
                    </Link>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar roles..." class="h-10 rounded-xl border-input bg-background pl-10 text-sm" @input="onSearch" />
                </div>
            </div>

            <div v-if="loading && !roles.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"><SkeletonCard v-for="i in 6" :key="i" /></div>
            <div v-else-if="!roles.data.length">
                <EmptyState title="No hay roles" description="Crea el primer rol." :icon="ShieldCheck" gradient="from-purple-500/20 to-pink-600/20" :action="{ label: 'Nuevo Rol', href: route('admin.seguridad.roles.create') }" />
            </div>

            <template v-else>
                <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(role, i) in roles.data" :key="role.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5 cursor-pointer" @click="showQuickView(role)">
                        <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-100" :class="getLight(i)" />
                        <div class="h-1 w-full bg-gradient-to-r rounded-t-2xl" :class="getColor(i)" />
                        <div class="mt-4 flex items-start justify-between relative">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl shadow-sm transition-transform duration-300 group-hover:scale-110" :class="'bg-gradient-to-br ' + getColor(i)"><Shield class="h-6 w-6 text-white" /></div>
                                <div><h3 class="text-base font-semibold text-foreground">{{ role.name }}</h3>
                                    <div class="flex items-center gap-3 mt-1"><span class="text-xs text-muted-foreground flex items-center gap-1"><Key class="h-3 w-3" />{{ role.permissions?.length ?? 0 }} permiso(s)</span>
                                    <span v-if="role.users_count !== undefined" class="text-xs text-muted-foreground flex items-center gap-1"><Users class="h-3 w-3" />{{ role.users_count }} usuario(s)</span></div></div>
                            </div>
                            <div v-if="role.name !== 'Super Admin'" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200" @click.stop>
                                <Link :href="route('admin.seguridad.roles.edit', role.id)"><Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm dark:bg-gray-800/90"><Pencil class="h-3.5 w-3.5" /></Button></Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm dark:bg-gray-800/90" @click="confirmDelete(role)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                        <div v-if="role.permissions?.length" class="mt-5 flex flex-wrap gap-1.5 relative">
                            <Badge v-for="perm in role.permissions.slice(0, 5)" :key="perm.name" class="border-0 bg-gradient-to-r from-purple-500/15 to-pink-600/15 text-purple-400 shadow-none text-[10px] font-medium px-2.5 py-1 rounded-lg">{{ perm.name }}</Badge>
                            <span v-if="role.permissions.length > 5" class="text-[10px] text-muted-foreground self-center ml-1">+{{ role.permissions.length - 5 }} más</span>
                        </div>
                        <p v-else class="mt-4 text-xs text-muted-foreground relative">Sin permisos asignados</p>
                    </div>
                </StaggerTransition>
            </template>

            <SlideOver :open="showSlideOver" title="Detalles del Rol" @close="showSlideOver = false">
                <div v-if="selectedRole" class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br" :class="getColor(selectedRole.id)"><Shield class="h-8 w-8 text-white" /></div>
                        <div><h3 class="text-lg font-semibold text-foreground">{{ selectedRole.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ selectedRole.permissions?.length ?? 0 }} permisos asignados</p></div>
                    </div>
                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">Permisos</p>
                        <div v-if="selectedRole.permissions?.length" class="flex flex-wrap gap-1.5">
                            <Badge v-for="perm in selectedRole.permissions" :key="perm.name" class="border-0 bg-gradient-to-r from-purple-500/15 to-pink-600/15 text-purple-400 shadow-none text-[10px] font-medium px-2.5 py-1 rounded-lg">{{ perm.name }}</Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Sin permisos</p>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <Link :href="route('admin.seguridad.roles.edit', selectedRole.id)" class="flex-1"><Button variant="outline" class="w-full rounded-xl border-border"><Pencil class="mr-2 h-4 w-4" />Editar</Button></Link>
                        <Button v-if="selectedRole.name !== 'Super Admin'" variant="destructive" size="icon" class="rounded-xl" @click="confirmDelete(selectedRole)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </div>
            </SlideOver>
        </div>
    </PageTransition>
</template>
