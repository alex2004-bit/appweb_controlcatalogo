<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Trash2, Search, FileText, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { PageTransition, StaggerTransition, ModuleHeader, StatusBadge } from '@/components/shared';
import type { PaginatedData } from '@/types';

type CotizacionItem = { id: number; codigo: string; total: string; estado: string; created_at: string; cliente: { id: number; nombre: string } | null; creador: { id: number; name: string }; };
const props = defineProps<{ cotizaciones: PaginatedData<CotizacionItem>; filters: { search: string | null; estado: string | null }; }>();

const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');
let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.cotizaciones.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true }), 400); }
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() { search.value = ''; estadoFilter.value = 'all'; router.get(route('admin.cotizaciones.index'), {}, { preserveState: true, replace: true }); }

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) { deleteId.value = id; showDelete.value = true; }
function destroy() { if (deleteId.value) router.delete(route('admin.cotizaciones.destroy', deleteId.value), { onSuccess: () => { showDelete.value = false; deleteId.value = null; } }); }

const estadoColors: Record<string, string> = {
    borrador: 'bg-gray-500/15 text-gray-400 border-gray-500/20',
    enviada: 'bg-blue-500/15 text-blue-500 border-blue-500/20',
    aprobada: 'bg-green-500/15 text-green-500 border-green-500/20',
    rechazada: 'bg-red-500/15 text-red-500 border-red-500/20',
};
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Cotizaciones" description="Presupuestos y cotizaciones para clientes" :icon="FileText" gradient="from-emerald-500 to-teal-600">
                <template #actions>
                    <Link :href="route('admin.cotizaciones.create')">
                        <Button class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-700 rounded-xl h-10">
                            <Plus class="mr-2 h-4 w-4" /> Nueva Cotización
                        </Button>
                    </Link>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar cotizaciones..." class="h-10 rounded-xl border-input bg-card pl-10 text-sm" @input="onSearch" />
                </div>
                <Select v-model="estadoFilter" @update:model-value="onSearch">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-card w-36"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas</SelectItem>
                        <SelectItem value="borrador">Borrador</SelectItem>
                        <SelectItem value="enviada">Enviada</SelectItem>
                        <SelectItem value="aprobada">Aprobada</SelectItem>
                        <SelectItem value="rechazada">Rechazada</SelectItem>
                    </SelectContent>
                </Select>
                <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-xl" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="c in cotizaciones.data" :key="c.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r" :class="c.estado === 'aprobada' ? 'from-green-500 to-emerald-600' : c.estado === 'rechazada' ? 'from-red-500 to-rose-600' : c.estado === 'enviada' ? 'from-blue-500 to-indigo-600' : 'from-gray-500 to-slate-600'" />
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-600/20">
                                    <FileText class="h-5 w-5 text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-foreground font-mono">{{ c.codigo }}</p>
                                    <p class="text-xs text-muted-foreground">{{ c.cliente?.nombre ?? 'Sin cliente' }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase" :class="estadoColors[c.estado] || ''">{{ c.estado }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">{{ c.created_at }}</span>
                            <span class="text-base font-bold text-emerald-400">${{ parseFloat(c.total).toFixed(2) }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-border/50 pt-3">
                            <span class="text-xs text-muted-foreground">Por: {{ c.creador?.name ?? '—' }}</span>
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('admin.cotizaciones.show', c.id)">
                                    <Button variant="secondary" size="sm" class="h-8 rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm"><Eye class="mr-1 h-3 w-3" />Ver</Button>
                                </Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm" @click="confirmDelete(c.id)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                    </div>
                </div>
            </StaggerTransition>

            <div v-if="!cotizaciones.data.length" class="flex flex-col items-center justify-center py-20 text-center">
                <FileText class="h-16 w-16 text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-semibold text-foreground">No hay cotizaciones</h3>
                <p class="mt-1 text-sm text-muted-foreground">Crea tu primera cotización.</p>
            </div>

            <Dialog :open="showDelete" @update:open="showDelete = $event">
                <DialogContent class="sm:max-w-md"><DialogHeader><DialogTitle>Eliminar cotización</DialogTitle><DialogDescription>¿Estás seguro? Esta acción no se puede deshacer.</DialogDescription></DialogHeader>
                    <DialogFooter><Button variant="outline" @click="showDelete = false">Cancelar</Button><Button variant="destructive" @click="destroy">Eliminar</Button></DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </PageTransition>
</template>
