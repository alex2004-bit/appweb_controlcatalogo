<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Plus, Eye, Trash2, Search, ShoppingCart, X, CreditCard, User, Clock } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PageTransition, StaggerTransition, ModuleHeader, EmptyState, SkeletonCard, SlideOver } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import type { PaginatedData } from '@/types';
import { useConfirm } from '@/composables/useConfirm';

type PedidoItem = { id: number; codigo: string; total: string; estado: string; pagado: boolean; metodo_pago: string | null; created_at: string; cliente: { id: number; nombre: string } | null; creador: { id: number; name: string }; };
const props = defineProps<{ pedidos: PaginatedData<PedidoItem>; filters: { search: string | null; estado: string | null }; }>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');
const estadoFilter = ref(props.filters.estado ?? 'all');
const loading = ref(false);
const selectedPedido = ref<PedidoItem | null>(null);
const showSlideOver = ref(false);

router.on('start', () => { loading.value = true; });
router.on('finish', () => { loading.value = false; });

let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(() => router.get(route('admin.pedidos.index'), { search: search.value || null, estado: estadoFilter.value === 'all' ? null : estadoFilter.value }, { preserveState: true, replace: true }), 400); }
const hasFilters = computed(() => search.value || (estadoFilter.value && estadoFilter.value !== 'all'));
function clearFilters() { search.value = ''; estadoFilter.value = 'all'; router.get(route('admin.pedidos.index'), {}, { preserveState: true, replace: true }); }

async function confirmDelete(pedido: PedidoItem) {
    const ok = await confirm({ title: 'Eliminar Pedido', text: `¿Eliminar el pedido ${pedido.codigo}?`, danger: true, confirmText: 'Sí, eliminar' });
    if (ok) router.delete(route('admin.pedidos.destroy', pedido.id), { onSuccess: () => success('Pedido eliminado') });
}

const estadoStyle: Record<string, string> = { pendiente: 'from-amber-500 to-yellow-600', procesando: 'from-blue-500 to-indigo-600', completado: 'from-green-500 to-emerald-600', cancelado: 'from-red-500 to-rose-600' };
const estiloBg: Record<string, string> = { pendiente: 'from-amber-500/10 to-yellow-600/10', procesando: 'from-blue-500/10 to-indigo-600/10', completado: 'from-green-500/10 to-emerald-600/10', cancelado: 'from-red-500/10 to-rose-600/10' };
const estadoColor: Record<string, string> = { pendiente: 'bg-amber-500/15 text-amber-500 border-amber-500/20', procesando: 'bg-blue-500/15 text-blue-500 border-blue-500/20', completado: 'bg-green-500/15 text-green-500 border-green-500/20', cancelado: 'bg-red-500/15 text-red-500 border-red-500/20' };
const estadoLabels: Record<string, string> = { pendiente: 'Pendiente', procesando: 'Procesando', completado: 'Completado', cancelado: 'Cancelado' };
const metodoPagoLabels: Record<string, string> = { efectivo: 'Efectivo', transferencia: 'Transferencia', tarjeta: 'Tarjeta', cheque: 'Cheque', deposito: 'Depósito', otro: 'Otro' };

function formatDate(d: string) { return d ? new Date(d).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' }) : ''; }
function showQuickView(p: PedidoItem) { selectedPedido.value = p; showSlideOver.value = true; }
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Pedidos" :description="`${pedidos.total} pedidos registrados`" :icon="ShoppingCart" gradient="from-cyan-500 to-blue-600">
                <template #actions>
                    <Link :href="route('admin.pedidos.create')">
                        <Button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl h-10 transition-all hover:shadow-xl active:scale-95"><Plus class="mr-2 h-4 w-4" /> Nuevo Pedido</Button>
                    </Link>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar por código o cliente..." class="h-10 rounded-xl border-input bg-background pl-10 text-sm" @input="onSearch" />
                </div>
                <Select v-model="estadoFilter" @update:model-value="onSearch">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-background w-36"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem v-for="(l, k) in estadoLabels" :key="k" :value="k">{{ l }}</SelectItem>
                    </SelectContent>
                </Select>
                <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-xl" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <div v-if="loading && !pedidos.data.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"><SkeletonCard v-for="i in 6" :key="i" /></div>
            <div v-else-if="!pedidos.data.length">
                <EmptyState title="No hay pedidos" description="Crea tu primer pedido." :icon="ShoppingCart" gradient="from-cyan-500/20 to-blue-600/20" :action="{ label: 'Nuevo Pedido', href: route('admin.pedidos.create') }" />
            </div>

            <template v-else>
                <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div v-for="p in pedidos.data" :key="p.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5 cursor-pointer" @click="showQuickView(p)">
                        <div class="h-1.5 w-full bg-gradient-to-r" :class="estadoStyle[p.estado] || 'from-gray-500 to-slate-600'" />
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br shadow-sm" :class="estiloBg[p.estado] || 'from-gray-500/10 to-slate-600/10'">
                                        <ShoppingCart class="h-6 w-6" :class="p.estado === 'completado' ? 'text-green-400' : p.estado === 'cancelado' ? 'text-red-400' : 'text-blue-400'" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold font-mono text-foreground">{{ p.codigo }}</p>
                                        <p class="text-xs text-muted-foreground mt-0.5 flex items-center gap-1"><User class="h-3 w-3" />{{ p.cliente?.nombre ?? '—' }}</p>
                                    </div>
                                </div>
                                <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-semibold" :class="estadoColor[p.estado] || ''">{{ estadoLabels[p.estado] || p.estado }}</span>
                            </div>
                            <div class="mt-5 flex items-end justify-between">
                                <div class="space-y-1.5 text-xs text-muted-foreground">
                                    <p class="flex items-center gap-1.5"><Clock class="h-3.5 w-3.5" />{{ formatDate(p.created_at) }}</p>
                                    <p v-if="p.metodo_pago" class="flex items-center gap-1.5"><CreditCard class="h-3.5 w-3.5" />{{ metodoPagoLabels[p.metodo_pago] || p.metodo_pago }}</p>
                                </div>
                                <p class="text-xl font-bold text-cyan-400">${{ parseFloat(p.total).toFixed(2) }}</p>
                            </div>
                            <div class="mt-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-y-1 group-hover:translate-y-0" @click.stop>
                                <Link :href="route('admin.pedidos.show', p.id)" class="flex-1">
                                    <Button variant="secondary" size="sm" class="w-full rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm dark:bg-gray-800/90"><Eye class="mr-1.5 h-3.5 w-3.5" />Ver Detalle</Button>
                                </Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm dark:bg-gray-800/90" @click="confirmDelete(p)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                    </div>
                </StaggerTransition>
            </template>

            <SlideOver :open="showSlideOver" title="Detalle del Pedido" @close="showSlideOver = false">
                <div v-if="selectedPedido" class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br" :class="estiloBg[selectedPedido.estado]">
                            <ShoppingCart class="h-8 w-8" :class="selectedPedido.estado === 'completado' ? 'text-green-400' : 'text-blue-400'" />
                        </div>
                        <div>
                            <p class="text-lg font-semibold font-mono text-foreground">{{ selectedPedido.codigo }}</p>
                            <Badge class="mt-1" :class="estadoColor[selectedPedido.estado] || ''">{{ estadoLabels[selectedPedido.estado] || selectedPedido.estado }}</Badge>
                        </div>
                    </div>
                    <div class="grid gap-4 rounded-xl bg-muted/30 p-4">
                        <div class="flex items-center gap-3"><User class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ selectedPedido.cliente?.nombre ?? '—' }}</span></div>
                        <div class="flex items-center gap-3"><CreditCard class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ (metodoPagoLabels[selectedPedido.metodo_pago ?? ''] || selectedPedido.metodo_pago || '—') }}</span></div>
                        <div class="flex items-center gap-3"><Clock class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ formatDate(selectedPedido.created_at) }}</span></div>
                    </div>
                    <div class="rounded-xl border border-border/40 p-4 text-center"><p class="text-3xl font-bold text-cyan-400">${{ parseFloat(selectedPedido.total).toFixed(2) }}</p><p class="text-xs text-muted-foreground mt-1">Total del pedido</p></div>
                    <div class="flex gap-2 pt-2">
                        <Link :href="route('admin.pedidos.show', selectedPedido.id)" class="flex-1"><Button variant="outline" class="w-full rounded-xl border-border"><Eye class="mr-2 h-4 w-4" />Ver Detalle Completo</Button></Link>
                        <Button variant="destructive" size="icon" class="rounded-xl" @click="confirmDelete(selectedPedido)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </div>
            </SlideOver>
        </div>
    </PageTransition>
</template>
