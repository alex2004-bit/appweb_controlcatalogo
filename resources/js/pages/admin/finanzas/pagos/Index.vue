<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { CreditCard, Plus, Eye, Pencil, Trash2, Search, X, DollarSign, Building2, CalendarDays } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PageTransition, StaggerTransition, ModuleHeader, EmptyState, SkeletonCard, SlideOver } from '@/components/shared';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps<{
    pagos: { data: Array<{ id: number; monto: string; metodo_pago: string; referencia: string | null; estado: string; fecha_pago: string; notas: string | null; comprobante: string | null; cotizacion: { id: number; codigo: string; total: string; cliente: { id: number; nombre: string } | null } | null }>; meta: { current_page: number; last_page: number; from: number; to: number; total: number; }; };
    filters: { search: string | null; metodo_pago: string | null; estado: string | null; fecha_desde: string | null; fecha_hasta: string | null; };
}>();
const { confirm, success } = useConfirm();

const search = ref(props.filters.search ?? '');
const metodoPago = ref(props.filters.metodo_pago ?? 'all');
const estadoFilter = ref(props.filters.estado ?? 'all');
const fechaDesde = ref(props.filters.fecha_desde ?? '');
const fechaHasta = ref(props.filters.fecha_hasta ?? '');
const loading = ref(false);
const selectedPago = ref<any>(null);
const showSlideOver = ref(false);

router.on('start', () => { loading.value = true; });
router.on('finish', () => { loading.value = false; });

let st: number | undefined;
function onSearch() { clearTimeout(st); st = window.setTimeout(applyFilter, 400); }
function applyFilter() { router.get(route('admin.finanzas.pagos.index'), { search: search.value || null, metodo_pago: metodoPago.value === 'all' ? null : metodoPago.value, estado: estadoFilter.value === 'all' ? null : estadoFilter.value, fecha_desde: fechaDesde.value || null, fecha_hasta: fechaHasta.value || null }, { preserveState: true, preserveScroll: true }); }
const hasFilters = computed(() => search.value || (metodoPago.value && metodoPago.value !== 'all') || (estadoFilter.value && estadoFilter.value !== 'all') || fechaDesde.value || fechaHasta.value);
function clearFilters() { search.value = ''; metodoPago.value = 'all'; estadoFilter.value = 'all'; fechaDesde.value = ''; fechaHasta.value = ''; router.get(route('admin.finanzas.pagos.index'), {}, { preserveState: true, replace: true }); }

async function confirmDelete(p: any) { const ok = await confirm({ title: 'Eliminar Pago', text: '¿Estás seguro?', danger: true, confirmText: 'Sí, eliminar' }); if (ok) router.delete(route('admin.finanzas.pagos.destroy', p.id), { preserveScroll: true, onSuccess: () => success('Pago eliminado') }); }

const metodoPagoLabels: Record<string, string> = { efectivo: 'Efectivo', transferencia: 'Transferencia', tarjeta: 'Tarjeta', cheque: 'Cheque', deposito: 'Depósito', otro: 'Otro' };
const estadoColors: Record<string, string> = { completado: 'bg-emerald-500/15 text-emerald-500 border-emerald-500/20', pendiente: 'bg-amber-500/15 text-amber-500 border-amber-500/20', rechazado: 'bg-red-500/15 text-red-500 border-red-500/20' };
const estadoBg: Record<string, string> = { completado: 'from-emerald-500/10 to-green-600/10', pendiente: 'from-amber-500/10 to-yellow-600/10', rechazado: 'from-red-500/10 to-rose-600/10' };
function formatDate(d: string) { return d ? new Date(d).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' }) : ''; }
function showQuickView(p: any) { selectedPago.value = p; showSlideOver.value = true; }
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Pagos" :description="`${pagos.meta.total} pagos registrados`" :icon="CreditCard" gradient="from-rose-500 to-red-600">
                <template #actions>
                    <Link :href="route('admin.finanzas.pagos.create')">
                        <Button class="bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-lg shadow-rose-500/25 rounded-xl h-10 transition-all hover:shadow-xl active:scale-95"><Plus class="mr-2 h-4 w-4" /> Nuevo Pago</Button>
                    </Link>
                </template>
            </ModuleHeader>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="search" placeholder="Buscar..." class="h-10 rounded-xl border-input bg-background pl-9 text-sm" @input="onSearch" />
                </div>
                <Select v-model="metodoPago" @update:model-value="applyFilter">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-background w-36"><SelectValue placeholder="Método" /></SelectTrigger>
                    <SelectContent><SelectItem value="all">Todos</SelectItem><SelectItem v-for="(l, k) in metodoPagoLabels" :key="k" :value="k">{{ l }}</SelectItem></SelectContent>
                </Select>
                <Select v-model="estadoFilter" @update:model-value="applyFilter">
                    <SelectTrigger class="h-10 rounded-xl border-input bg-background w-32"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent><SelectItem value="all">Todos</SelectItem><SelectItem value="completado">Completado</SelectItem><SelectItem value="pendiente">Pendiente</SelectItem><SelectItem value="rechazado">Rechazado</SelectItem></SelectContent>
                </Select>
                <Input v-model="fechaDesde" type="date" class="h-10 w-[140px] rounded-xl border-input bg-background" @change="applyFilter" />
                <Input v-model="fechaHasta" type="date" class="h-10 w-[140px] rounded-xl border-input bg-background" @change="applyFilter" />
                <Button v-if="hasFilters" variant="ghost" size="sm" class="h-10 rounded-xl" @click="clearFilters"><X class="mr-1.5 h-4 w-4" />Limpiar</Button>
            </div>

            <div v-if="loading && !pagos.data.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"><SkeletonCard v-for="i in 6" :key="i" /></div>
            <div v-else-if="!pagos.data.length">
                <EmptyState title="No hay pagos" description="Registra el primer pago." :icon="CreditCard" gradient="from-rose-500/20 to-red-600/20" :action="{ label: 'Nuevo Pago', href: route('admin.finanzas.pagos.create') }" />
            </div>

            <template v-else>
                <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div v-for="p in pagos.data" :key="p.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5 cursor-pointer" @click="showQuickView(p)">
                        <div class="h-1.5 w-full bg-gradient-to-r" :class="p.estado === 'completado' ? 'from-emerald-500 to-green-600' : p.estado === 'pendiente' ? 'from-amber-500 to-yellow-600' : 'from-red-500 to-rose-600'" />
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br shadow-sm" :class="estadoBg[p.estado] || 'from-gray-500/10'">
                                        <DollarSign class="h-6 w-6" :class="p.estado === 'completado' ? 'text-emerald-400' : p.estado === 'pendiente' ? 'text-amber-400' : 'text-red-400'" />
                                    </div>
                                    <div><p class="text-sm font-semibold text-foreground">Pago #{{ p.id }}</p><p v-if="p.cotizacion" class="text-xs text-muted-foreground font-mono">{{ p.cotizacion.codigo }}</p></div>
                                </div>
                                <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-semibold" :class="estadoColors[p.estado] || ''">{{ p.estado }}</span>
                            </div>
                            <div class="mt-5 flex items-center justify-between">
                                <div class="space-y-1.5 text-xs text-muted-foreground">
                                    <p class="flex items-center gap-1.5"><Building2 class="h-3.5 w-3.5" />{{ p.cotizacion?.cliente?.nombre ?? '—' }}</p>
                                    <p class="flex items-center gap-1.5"><CreditCard class="h-3.5 w-3.5" />{{ metodoPagoLabels[p.metodo_pago] || p.metodo_pago }}</p>
                                    <p class="flex items-center gap-1.5"><CalendarDays class="h-3.5 w-3.5" />{{ formatDate(p.fecha_pago) }}</p>
                                </div>
                                <p class="text-2xl font-bold" :class="p.estado === 'completado' ? 'text-emerald-400' : p.estado === 'pendiente' ? 'text-amber-400' : 'text-red-400'">${{ parseFloat(p.monto).toFixed(2) }}</p>
                            </div>
                            <div class="mt-4 flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200 justify-end translate-y-1 group-hover:translate-y-0" @click.stop>
                                <Link :href="route('admin.finanzas.pagos.show', p.id)"><Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm dark:bg-gray-800/90"><Eye class="h-3.5 w-3.5" /></Button></Link>
                                <Link :href="route('admin.finanzas.pagos.edit', p.id)"><Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-gray-700 shadow-sm dark:bg-gray-800/90"><Pencil class="h-3.5 w-3.5" /></Button></Link>
                                <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm dark:bg-gray-800/90" @click="confirmDelete(p)"><Trash2 class="h-3.5 w-3.5" /></Button>
                            </div>
                        </div>
                    </div>
                </StaggerTransition>
            </template>

            <SlideOver :open="showSlideOver" title="Detalle del Pago" @close="showSlideOver = false">
                <div v-if="selectedPago" class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br" :class="estadoBg[selectedPago.estado]"><DollarSign class="h-8 w-8" :class="selectedPago.estado === 'completado' ? 'text-emerald-400' : 'text-amber-400'" /></div>
                        <div><p class="text-lg font-semibold text-foreground">Pago #{{ selectedPago.id }}</p><Badge class="mt-1" :class="estadoColors[selectedPago.estado] || ''">{{ selectedPago.estado }}</Badge></div>
                    </div>
                    <div class="grid gap-4 rounded-xl bg-muted/30 p-4">
                        <div class="flex items-center gap-3"><Building2 class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ selectedPago.cotizacion?.cliente?.nombre ?? '—' }}</span></div>
                        <div class="flex items-center gap-3"><CreditCard class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ metodoPagoLabels[selectedPago.metodo_pago] || selectedPago.metodo_pago }}</span></div>
                        <div class="flex items-center gap-3"><CalendarDays class="h-4 w-4 text-muted-foreground" /><span class="text-sm">{{ formatDate(selectedPago.fecha_pago) }}</span></div>
                        <div v-if="selectedPago.referencia" class="flex items-center gap-3"><span class="text-sm font-mono">Ref: {{ selectedPago.referencia }}</span></div>
                    </div>
                    <div class="rounded-xl border border-border/40 p-4 text-center"><p class="text-3xl font-bold text-emerald-400">${{ parseFloat(selectedPago.monto).toFixed(2) }}</p><p class="text-xs text-muted-foreground mt-1">Monto del pago</p></div>
                    <div class="flex gap-2 pt-2">
                        <Link :href="route('admin.finanzas.pagos.edit', selectedPago.id)" class="flex-1"><Button variant="outline" class="w-full rounded-xl border-border"><Pencil class="mr-2 h-4 w-4" />Editar</Button></Link>
                        <Button variant="destructive" size="icon" class="rounded-xl" @click="confirmDelete(selectedPago)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </div>
            </SlideOver>
        </div>
    </PageTransition>
</template>
