<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { MessageSquare, Search, Eye, Trash2, Mail, User, X, Filter } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { PageTransition, StaggerTransition, ModuleHeader } from '@/components/shared';
import type { PaginatedData } from '@/types';

type Consulta = { id: number; nombre: string; email: string; mensaje: string; tipo: string; estado: string; leido: boolean; created_at: string; };
const props = defineProps<{ consultas: PaginatedData<Consulta>; filters: { search: string | null; estado: string | null }; }>();

const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');
let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.consultas.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true }), 400); }
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() { search.value = ''; estadoFilter.value = 'all'; router.get(route('admin.consultas.index'), {}, { preserveState: true, replace: true }); }

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) { deleteId.value = id; showDelete.value = true; }
function destroy() { if (deleteId.value) router.delete(route('admin.consultas.destroy', deleteId.value), { onSuccess: () => { showDelete.value = false; deleteId.value = null; } }); }

const estadoColors: Record<string, string> = {
    pendiente: 'bg-yellow-500/15 text-yellow-500 border-yellow-500/20',
    en_proceso: 'bg-blue-500/15 text-blue-500 border-blue-500/20',
    respondido: 'bg-green-500/15 text-green-500 border-green-500/20',
    cerrado: 'bg-gray-500/15 text-gray-400 border-gray-500/20',
};
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Consultas" description="Consultas y mensajes de clientes" :icon="MessageSquare" gradient="from-purple-500 to-pink-600">
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Select v-model="estadoFilter" @update:model-value="onSearch">
                            <SelectTrigger class="h-10 rounded-xl border-input bg-card w-36"><SelectValue placeholder="Estado" /></SelectTrigger>
                            <SelectContent><SelectItem value="all">Todas</SelectItem><SelectItem value="pendiente">Pendiente</SelectItem><SelectItem value="en_proceso">En Proceso</SelectItem><SelectItem value="respondido">Respondido</SelectItem><SelectItem value="cerrado">Cerrado</SelectItem></SelectContent>
                        </Select>
                    </div>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar por nombre o email..." class="h-10 rounded-xl border-input bg-card pl-10 text-sm" @input="onSearch" />
                </div>
                <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-xl" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="c in consultas.data" :key="c.id" :class="['group relative overflow-hidden rounded-2xl border bg-card shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5', c.leido ? 'border-border/50' : 'border-purple-500/30 bg-purple-500/[0.02]']">
                    <div v-if="!c.leido" class="absolute top-3 left-3 h-2 w-2 rounded-full bg-purple-500 animate-pulse" />
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-600 text-sm font-bold text-white shadow-sm">
                                    {{ c.nombre.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-foreground">{{ c.nombre }}</p>
                                    <p class="text-xs text-muted-foreground flex items-center gap-1"><Mail class="h-3 w-3" />{{ c.email }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold" :class="estadoColors[c.estado] || 'bg-gray-500/15 text-gray-400'">{{ c.estado }}</span>
                        </div>
                        <p class="mt-3 text-sm text-muted-foreground line-clamp-3 leading-relaxed">{{ c.mensaje }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-[10px] text-muted-foreground/50">{{ c.created_at }}</span>
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('admin.consultas.show', c.id)">
                                    <Button variant="secondary" size="sm" class="h-8 rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm text-xs"><Eye class="mr-1 h-3 w-3" />Ver</Button>
                                </Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm" @click="confirmDelete(c.id)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                    </div>
                </div>
            </StaggerTransition>

            <div v-if="!consultas.data.length" class="flex flex-col items-center justify-center py-20 text-center">
                <MessageSquare class="h-16 w-16 text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-semibold text-foreground">No hay consultas</h3>
                <p class="mt-1 text-sm text-muted-foreground">Las consultas de clientes aparecerán aquí.</p>
            </div>

            <Dialog :open="showDelete" @update:open="showDelete = $event">
                <DialogContent class="sm:max-w-md"><DialogHeader><DialogTitle>Eliminar consulta</DialogTitle><DialogDescription>¿Estás seguro? Esta acción no se puede deshacer.</DialogDescription></DialogHeader>
                    <DialogFooter><Button variant="outline" @click="showDelete = false">Cancelar</Button><Button variant="destructive" @click="destroy">Eliminar</Button></DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </PageTransition>
</template>
