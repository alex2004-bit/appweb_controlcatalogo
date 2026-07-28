<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, User, Building2, Search, Users, Mail, Phone, X, FileText, MessageSquare, Grid3X3, List, Eye } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable, Pagination, PageTransition, StaggerTransition, ModuleHeader, EmptyState, SkeletonCard, SlideOver } from '@/components/shared';
import type { PaginatedData } from '@/types';
import { useConfirm } from '@/composables/useConfirm';

type ClienteItem = { id: number; tipo_persona: string; nombre: string; email: string | null; telefono: string | null; documento: string | null; empresa: string | null; cotizaciones_count: number; consultas_count: number; };
const props = defineProps<{ clientes: PaginatedData<ClienteItem>; filters: { search: string | null; tipo_persona: string | null }; }>();
const { confirm, success } = useConfirm();

const viewMode = ref<'grid' | 'list'>('grid');
const search = ref(props.filters.search ?? '');
const tipoFilter = ref(props.filters.tipo_persona ?? 'all');
const loading = ref(false);
const selectedCliente = ref<ClienteItem | null>(null);
const showSlideOver = ref(false);

router.on('start', () => { loading.value = true; });
router.on('finish', () => { loading.value = false; });

let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.clientes.index'), { search: search.value || null, tipo_persona: tipoFilter.value === 'all' ? null : tipoFilter.value }, { preserveState: true, replace: true }), 400); }
const hasFilters = computed(() => search.value || (tipoFilter.value && tipoFilter.value !== 'all'));
function clearFilters() { search.value = ''; tipoFilter.value = 'all'; router.get(route('admin.clientes.index'), {}, { preserveState: true, replace: true }); }

async function confirmDelete(cliente: ClienteItem) {
    const ok = await confirm({ title: 'Eliminar Cliente', text: `¿Estás seguro de eliminar a "${cliente.nombre}"?`, danger: true, confirmText: 'Sí, eliminar' });
    if (ok) router.delete(route('admin.clientes.destroy', cliente.id), { onSuccess: () => success('Cliente eliminado', 'Se ha eliminado correctamente.') });
}

const columns = [{ key: 'nombre', label: 'Nombre' }, { key: 'documento', label: 'Documento' }, { key: 'tipo_persona', label: 'Tipo' }, { key: 'email', label: 'Email' }, { key: 'telefono', label: 'Teléfono' }, { key: 'cotizaciones_count', label: 'Cotizaciones' }];

function showQuickView(c: ClienteItem) { selectedCliente.value = c; showSlideOver.value = true; }

const clientTabs = [
    { key: 'clientes', label: 'Clientes', icon: Users, active: true },
    { key: 'consultas', label: 'Consultas', icon: MessageSquare, route: 'admin.consultas.index' },
    { key: 'cotizaciones', label: 'Cotizaciones', icon: FileText, route: 'admin.cotizaciones.index' },
];
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Clientes" :description="`${clientes.total} clientes registrados`" :icon="Users" gradient="from-emerald-500 to-teal-600">
                <template #actions>
                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex items-center rounded-xl border border-border bg-card p-1 shadow-sm">
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="viewMode === 'grid' ? 'bg-accent shadow-sm' : 'text-muted-foreground'" @click="viewMode = 'grid'"><Grid3X3 class="h-4 w-4" /></Button>
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="viewMode === 'list' ? 'bg-accent shadow-sm' : 'text-muted-foreground'" @click="viewMode = 'list'"><List class="h-4 w-4" /></Button>
                        </div>
                        <Link :href="route('admin.clientes.create')">
                            <Button class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 rounded-xl h-10 transition-all hover:shadow-xl active:scale-95"><Plus class="mr-2 h-4 w-4" /> Nuevo Cliente</Button>
                        </Link>
                    </div>
                </template>
            </ModuleHeader>

            <div class="flex gap-1.5 rounded-2xl border border-border/50 bg-card p-1.5 shadow-sm overflow-x-auto">
                <Link v-for="tab in clientTabs" :key="tab.key" :href="tab.active ? '#' : route(tab.route!)" :class="['flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200', tab.active ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md' : 'text-muted-foreground hover:text-foreground hover:bg-accent/50']">
                    <component :is="tab.icon" class="h-4 w-4" />
                    <span class="hidden sm:inline">{{ tab.label }}</span>
                </Link>
            </div>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar por nombre, email o doc..." class="h-10 rounded-xl border-input bg-background pl-10 text-sm" @input="onSearch" />
                </div>
                <Select v-model="tipoFilter" @update:model-value="onSearch">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-background w-36"><SelectValue placeholder="Tipo" /></SelectTrigger>
                    <SelectContent><SelectItem value="all">Todos</SelectItem><SelectItem value="natural">Natural</SelectItem><SelectItem value="juridica">Jurídica</SelectItem></SelectContent>
                </Select>
                <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-xl text-muted-foreground" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <div v-if="loading && !clientes.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <SkeletonCard v-for="i in 8" :key="i" />
            </div>

            <div v-else-if="!clientes.data.length">
                <EmptyState title="No hay clientes" description="Registra tu primer cliente para empezar." :icon="Users" gradient="from-emerald-500/20 to-teal-600/20" :action="{ label: 'Nuevo Cliente', href: route('admin.clientes.create') }" />
            </div>

            <template v-else>
                <div v-if="viewMode === 'grid'">
                    <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div v-for="c in clientes.data" :key="c.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5 hover:border-emerald-500/30 cursor-pointer" @click="showQuickView(c)">
                            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-600 opacity-80" />
                            <div class="flex items-start justify-between">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500/20 to-teal-600/20 ring-2 ring-emerald-500/10"><User class="h-7 w-7 text-emerald-400" /></div>
                                <Badge variant="secondary" class="rounded-lg text-[10px] font-semibold bg-muted/50">{{ c.tipo_persona === 'natural' ? 'Natural' : 'Jurídica' }}</Badge>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-foreground">{{ c.nombre }}</h3>
                            <p v-if="c.empresa" class="mt-1 text-xs text-muted-foreground flex items-center gap-1.5"><Building2 class="h-3.5 w-3.5 shrink-0" />{{ c.empresa }}</p>
                            <div class="mt-4 space-y-2">
                                <p v-if="c.email" class="flex items-center gap-2 text-xs text-muted-foreground"><Mail class="h-3.5 w-3.5 shrink-0" /><span class="truncate">{{ c.email }}</span></p>
                                <p v-if="c.telefono" class="flex items-center gap-2 text-xs text-muted-foreground"><Phone class="h-3.5 w-3.5 shrink-0" />{{ c.telefono }}</p>
                            </div>
                            <div class="mt-5 pt-3 border-t border-border/40 flex items-center gap-4 text-xs text-muted-foreground">
                                <span class="flex items-center gap-1.5"><FileText class="h-3.5 w-3.5" /><span class="font-medium">{{ c.cotizaciones_count }}</span> cot.</span>
                                <span class="flex items-center gap-1.5"><MessageSquare class="h-3.5 w-3.5" /><span class="font-medium">{{ c.consultas_count }}</span> cons.</span>
                            </div>
                            <div class="mt-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-y-1 group-hover:translate-y-0" @click.stop>
                                <Link :href="route('admin.clientes.edit', c.id)" class="flex-1"><Button variant="outline" size="sm" class="w-full rounded-xl border-border"><Pencil class="mr-1.5 h-3.5 w-3.5" />Editar</Button></Link>
                                <Button variant="destructive" size="sm" class="rounded-xl" @click="confirmDelete(c)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                    </StaggerTransition>
                    <Pagination :pagination="clientes" class="mt-4" />
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm">
                    <DataTable :columns="columns" :data="clientes">
                        <template #cell:nombre="{ row }">
                            <div class="flex items-center gap-3 cursor-pointer" @click="showQuickView(row)">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500/20 to-teal-600/20 shadow-sm"><User class="h-5 w-5 text-emerald-400" /></div>
                                <div><p class="font-medium text-foreground">{{ row.nombre }}</p><p v-if="row.empresa" class="flex items-center gap-1 text-xs text-muted-foreground"><Building2 class="h-3 w-3" />{{ row.empresa }}</p></div>
                            </div>
                        </template>
                        <template #cell:tipo_persona="{ row }"><Badge variant="secondary" class="rounded-lg bg-muted/50">{{ row.tipo_persona === 'natural' ? 'Natural' : 'Jurídica' }}</Badge></template>
                        <template #actions="{ row }">
                            <div class="flex justify-end gap-2">
                                <Link :href="route('admin.clientes.edit', row.id)"><Button variant="outline" size="sm" class="h-9 w-9 rounded-lg border-border"><Pencil class="h-4 w-4" /></Button></Link>
                                <Button variant="destructive" size="sm" class="h-9 w-9 rounded-lg" @click="confirmDelete(row)"><Trash2 class="h-4 w-4" /></Button>
                            </div>
                        </template>
                    </DataTable>
                    <div class="border-t border-border/50 p-4"><Pagination :pagination="clientes" /></div>
                </div>
            </template>

            <SlideOver :open="showSlideOver" title="Detalles del Cliente" @close="showSlideOver = false">
                <div v-if="selectedCliente" class="space-y-5">
                    <div class="flex flex-col items-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-2xl font-bold text-white shadow-lg"><User class="h-8 w-8" /></div>
                        <h3 class="mt-3 text-lg font-semibold text-foreground">{{ selectedCliente.nombre }}</h3>
                        <Badge class="mt-1 rounded-lg bg-muted/50">{{ selectedCliente.tipo_persona === 'natural' ? 'Persona Natural' : 'Persona Jurídica' }}</Badge>
                    </div>
                    <div class="grid gap-4 rounded-xl bg-muted/30 p-4">
                        <div class="flex items-center gap-3"><Mail class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ selectedCliente.email ?? '—' }}</span></div>
                        <div class="flex items-center gap-3"><Phone class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ selectedCliente.telefono ?? '—' }}</span></div>
                        <div class="flex items-center gap-3"><Building2 class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ selectedCliente.empresa ?? '—' }}</span></div>
                        <div class="flex items-center gap-3"><FileText class="h-4 w-4 text-muted-foreground" /><span class="text-sm font-mono">{{ selectedCliente.documento ?? '—' }}</span></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl border border-border/40 p-3 text-center"><p class="text-2xl font-bold text-emerald-400">{{ selectedCliente.cotizaciones_count }}</p><p class="text-xs text-muted-foreground">Cotizaciones</p></div>
                        <div class="rounded-xl border border-border/40 p-3 text-center"><p class="text-2xl font-bold text-purple-400">{{ selectedCliente.consultas_count }}</p><p class="text-xs text-muted-foreground">Consultas</p></div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <Link :href="route('admin.clientes.edit', selectedCliente.id)" class="flex-1"><Button variant="outline" class="w-full rounded-xl border-border"><Pencil class="mr-2 h-4 w-4" />Editar</Button></Link>
                        <Button variant="destructive" class="rounded-xl" @click="confirmDelete(selectedCliente)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </div>
            </SlideOver>
        </div>
    </PageTransition>
</template>
