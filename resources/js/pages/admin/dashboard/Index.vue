<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Filler,
} from 'chart.js';
import { ModuleHeader, StatsGrid, PageTransition } from '@/components/shared';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    LayoutGrid,
    ArrowRight,
    Users,
    Package,
    Wrench,
    MessageSquare,
    FileText,
    CreditCard,
    DollarSign,
    TrendingUp,
    ShoppingCart,
    Clock,
    FolderTree,
    UserCircle,
    BarChart3,
    Zap,
    Activity,
    Store,
    Percent,
} from '@lucide/vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Filler);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentTeam = computed(() => page.props.currentTeam);
const teamSlug = computed(() => currentTeam?.value?.slug ?? '');

const props = defineProps<{
    stats: {
        categorias: number;
        productos: number;
        servicios: number;
        consultas: number;
        cotizaciones: number;
        pagos: number;
        ingresos_mes: number;
        pagos_pendientes: number;
        ingresos_anio?: number;
        total_clientes?: number;
        cotizaciones_mes?: number;
    };
    ultimasConsultas: Array<{
        id: number;
        nombre: string;
        email: string;
        mensaje: string;
        tipo: string;
        estado: string;
        leido: boolean;
        created_at: string;
        cliente?: { id: number; nombre: string } | null;
    }>;
    ultimasCotizaciones: Array<{
        id: number;
        codigo: string;
        total: string;
        estado: string;
        created_at: string;
        cliente: { id: number; nombre: string } | null;
        creador: { id: number; name: string };
    }>;
    ultimosPagos: Array<{
        id: number;
        monto: string;
        metodo_pago: string;
        fecha_pago: string;
        cotizacion: { id: number; codigo: string; cliente: { nombre: string } | null } | null;
    }>;
    ingresosUltimos12?: Array<{ mes: string; total: number }>;
}>();

const statCards = computed(() => [
    { label: 'Categorías', value: props.stats.categorias, icon: FolderTree, color: 'bg-gradient-to-br from-cyan-500 to-teal-600', trend: props.stats.categorias > 0 ? 'up' as const : 'neutral' as const, trendValue: props.stats.categorias + ' total' },
    { label: 'Productos', value: props.stats.productos, icon: Package, color: 'bg-gradient-to-br from-blue-500 to-indigo-600', trend: props.stats.productos > 0 ? 'up' as const : 'neutral' as const, trendValue: 'en catálogo' },
    { label: 'Clientes', value: props.stats.total_clientes ?? 0, icon: Users, color: 'bg-gradient-to-br from-emerald-500 to-teal-600', trend: (props.stats.total_clientes ?? 0) > 0 ? 'up' as const : 'neutral' as const, trendValue: 'registrados' },
    { label: 'Cotizaciones', value: props.stats.cotizaciones, icon: FileText, color: 'bg-gradient-to-br from-emerald-500 to-teal-600', trend: 'up' as const, trendValue: 'emitidas' },
    { label: 'Consultas', value: props.stats.consultas, icon: MessageSquare, color: 'bg-gradient-to-br from-purple-500 to-pink-600', trend: props.stats.consultas > 0 ? 'up' as const : 'neutral' as const, trendValue: 'recibidas' },
    { label: 'Pagos', value: props.stats.pagos, icon: CreditCard, color: 'bg-gradient-to-br from-rose-500 to-red-600', trend: 'neutral' as const, trendValue: 'registrados' },
    { label: 'Ingresos del Mes', value: `$${(props.stats.ingresos_mes || 0).toFixed(2)}`, icon: DollarSign, color: 'bg-gradient-to-br from-green-500 to-emerald-600', trend: (props.stats.ingresos_mes || 0) > 0 ? 'up' as const : 'neutral' as const, trendValue: ((props.stats.ingresos_anio || 0) > 0) ? `$${(props.stats.ingresos_anio || 0).toFixed(2)} año` : '' },
    { label: 'Por Cobrar', value: `$${(props.stats.pagos_pendientes || 0).toFixed(2)}`, icon: Clock, color: 'bg-gradient-to-br from-orange-500 to-red-600', trend: 'down' as const, trendValue: 'pendiente' },
]);

const chartData = computed(() => ({
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    datasets: [{
        label: 'Ingresos',
        data: props.ingresosUltimos12
            ? props.ingresosUltimos12.map(i => i.total)
            : [0, 0, 0, 0, 0, props.stats.ingresos_mes || 0, 0, 0, 0, 0, 0, 0],
        borderColor: '#06b6d4',
        backgroundColor: (ctx: any) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(6, 182, 212, 0.3)');
            gradient.addColorStop(1, 'rgba(6, 182, 212, 0)');
            return gradient;
        },
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#06b6d4',
        pointBorderColor: '#083344',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: '#22d3ee',
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart' as const },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(0,0,0,0.9)',
            padding: 12,
            cornerRadius: 10,
            titleFont: { size: 12 },
            bodyFont: { size: 13 },
            callbacks: {
                label: (ctx: any) => `$${ctx.parsed.y.toFixed(2)}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#64748b', font: { size: 11 } },
        },
        y: {
            grid: { color: 'rgba(148, 163, 184, 0.08)' },
            ticks: { color: '#64748b', font: { size: 11 }, callback: (v: any) => '$' + v },
            beginAtZero: true,
        },
    },
    interaction: { intersect: false, mode: 'index' as const },
};

const estadoColors: Record<string, string> = {
    pendiente: 'bg-yellow-500/15 text-yellow-400 border-yellow-500/20',
    en_proceso: 'bg-blue-500/15 text-blue-400 border-blue-500/20',
    respondido: 'bg-green-500/15 text-green-400 border-green-500/20',
    cerrado: 'bg-gray-500/15 text-gray-400 border-gray-500/20',
    borrador: 'bg-gray-500/15 text-gray-400 border-gray-500/20',
    enviada: 'bg-blue-500/15 text-blue-400 border-blue-500/20',
    aprobada: 'bg-green-500/15 text-green-400 border-green-500/20',
    rechazada: 'bg-red-500/15 text-red-400 border-red-500/20',
    completado: 'bg-green-500/15 text-green-400 border-green-500/20',
    parcial: 'bg-yellow-500/15 text-yellow-400 border-yellow-500/20',
    pagado: 'bg-green-500/15 text-green-400 border-green-500/20',
};

const metodoPagoLabels: Record<string, string> = {
    efectivo: 'Efectivo',
    transferencia: 'Transferencia',
    tarjeta: 'Tarjeta',
    cheque: 'Cheque',
    deposito: 'Depósito',
    otro: 'Otro',
};

function goTo(routeName: string) {
    if (teamSlug.value) {
        router.visit(route(routeName, teamSlug.value));
    }
}

function formatDate(dateStr: string) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' });
}

function getInitials(name: string) {
    return name?.charAt(0).toUpperCase() ?? '?';
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '' }]">
        <PageTransition>
            <div class="flex flex-col gap-6 p-4 sm:p-6">
                <ModuleHeader
                    title="Panel de Control"
                    :description="`Bienvenido, ${user?.name} — ${currentTeam?.name}`"
                    :icon="LayoutGrid"
                    gradient="from-cyan-500 to-blue-600"
                >
                    <template #actions>
                        <div class="hidden sm:flex items-center gap-2 rounded-xl border border-cyan-500/20 bg-cyan-500/5 px-4 py-2">
                            <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                            <span class="text-xs text-cyan-400 font-medium">Sistema activo</span>
                        </div>
                    </template>
                </ModuleHeader>

                <StatsGrid :stats="statCards" :columns="4" />

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 rounded-2xl border border-border/50 bg-card p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20">
                                    <TrendingUp class="h-5 w-5 text-cyan-400" />
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-foreground">Ingresos Anuales</h2>
                                    <p class="text-xs text-muted-foreground">Evolución mensual de ingresos</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg bg-emerald-500/10 px-3 py-1.5">
                                <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                                <span class="text-xs font-semibold text-emerald-400">$ {{ ((props.stats.ingresos_anio || props.stats.ingresos_mes || 0)).toFixed(2) }}</span>
                            </div>
                        </div>
                        <div class="h-72">
                            <Line v-if="(props.ingresosUltimos12 || []).some(i => i.total > 0) || (props.stats.ingresos_mes || 0) > 0" :data="chartData" :options="chartOptions" />
                            <div v-else class="flex h-full items-center justify-center">
                                <div class="text-center">
                                    <BarChart3 class="mx-auto h-12 w-12 text-muted-foreground/30 mb-3" />
                                    <p class="text-sm text-muted-foreground">Aún no hay datos de ingresos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border/50 bg-card p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-600/20">
                                    <MessageSquare class="h-5 w-5 text-purple-400" />
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-foreground">Consultas</h2>
                                    <p class="text-xs text-muted-foreground">Últimas recibidas</p>
                                </div>
                            </div>
                            <Link v-if="teamSlug" :href="route('admin.consultas.index', teamSlug)" class="flex items-center gap-1 text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                                Ver <ArrowRight class="h-3 w-3" />
                            </Link>
                        </div>
                        <div class="space-y-2 max-h-80 overflow-y-auto scrollbar-thin">
                            <div v-for="consulta in ultimasConsultas.slice(0, 6)" :key="consulta.id" class="group flex items-start gap-3 rounded-xl border border-border/40 bg-muted/20 p-3 transition-all duration-200 hover:bg-accent/50 hover:border-purple-500/30 hover:shadow-sm cursor-pointer">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-600 text-sm font-bold text-white shadow-sm">
                                    {{ getInitials(consulta.nombre) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-medium text-foreground">{{ consulta.nombre }}</p>
                                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase" :class="estadoColors[consulta.estado] || 'bg-gray-500/15 text-gray-400'">
                                            {{ consulta.estado }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 line-clamp-1 text-xs text-muted-foreground">{{ consulta.mensaje }}</p>
                                    <p class="mt-1 text-[10px] text-muted-foreground/50">{{ formatDate(consulta.created_at) }}</p>
                                </div>
                            </div>
                            <p v-if="!ultimasConsultas.length" class="py-8 text-center text-sm text-muted-foreground">No hay consultas recibidas.</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-border/50 bg-card p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-600/20">
                                    <FileText class="h-5 w-5 text-emerald-400" />
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-foreground">Cotizaciones</h2>
                                    <p class="text-xs text-muted-foreground">Últimas emitidas</p>
                                </div>
                            </div>
                            <Link v-if="teamSlug" :href="route('admin.cotizaciones.index', teamSlug)" class="flex items-center gap-1 text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                                Ver <ArrowRight class="h-3 w-3" />
                            </Link>
                        </div>
                        <div class="space-y-2">
                            <div v-for="cot in ultimasCotizaciones.slice(0, 5)" :key="cot.id" class="flex items-center gap-3 rounded-xl border border-border/40 bg-muted/20 p-3 transition-all duration-200 hover:bg-accent/50 hover:border-emerald-500/30 hover:shadow-sm">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-bold text-white shadow-sm">
                                    {{ cot.codigo?.slice(-3) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-medium text-foreground">{{ cot.cliente?.nombre ?? 'Sin cliente' }}</p>
                                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase" :class="estadoColors[cot.estado] || 'bg-gray-500/15 text-gray-400'">
                                            {{ cot.estado }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-muted-foreground mt-0.5">
                                        <span class="font-mono text-[10px] bg-muted/50 px-1.5 py-0.5 rounded">{{ cot.codigo }}</span>
                                        <span class="text-emerald-400 font-semibold">${{ parseFloat(cot.total).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!ultimasCotizaciones.length" class="py-8 text-center text-sm text-muted-foreground">No hay cotizaciones creadas.</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border/50 bg-card p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-green-500/20 to-emerald-600/20">
                                    <DollarSign class="h-5 w-5 text-green-400" />
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-foreground">Pagos</h2>
                                    <p class="text-xs text-muted-foreground">Últimos registrados</p>
                                </div>
                            </div>
                            <Link v-if="teamSlug" :href="route('admin.finanzas.pagos.index', teamSlug)" class="flex items-center gap-1 text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                                Ver <ArrowRight class="h-3 w-3" />
                            </Link>
                        </div>
                        <div class="space-y-2">
                            <div v-for="pago in ultimosPagos.slice(0, 5)" :key="pago.id" class="flex items-center gap-3 rounded-xl border border-border/40 bg-muted/20 p-3 transition-all duration-200 hover:bg-accent/50 hover:border-green-500/30 hover:shadow-sm">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-sm">
                                    <DollarSign class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-medium text-foreground">{{ pago.cotizacion?.cliente?.nombre ?? '—' }}</p>
                                        <span class="shrink-0 text-sm font-bold text-green-400">${{ parseFloat(pago.monto).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-muted-foreground mt-0.5">
                                        <span class="bg-muted/50 px-1.5 py-0.5 rounded font-mono text-[10px]">{{ pago.cotizacion?.codigo ?? '—' }}</span>
                                        <span>{{ metodoPagoLabels[pago.metodo_pago] || pago.metodo_pago }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!ultimosPagos.length" class="py-8 text-center text-sm text-muted-foreground">No hay pagos registrados.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/50 bg-card p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20">
                            <Zap class="h-5 w-5 text-cyan-400" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-foreground">Acceso Rápido</h2>
                            <p class="text-xs text-muted-foreground">Módulos más utilizados</p>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <Link v-if="teamSlug" :href="route('admin.catalogo.categorias.index', teamSlug)" class="group flex items-center gap-3 rounded-xl border border-border/40 bg-muted/20 p-4 transition-all duration-200 hover:bg-gradient-to-r hover:from-cyan-500/10 hover:to-teal-600/10 hover:border-cyan-500/30 hover:shadow-sm hover:-translate-y-0.5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-teal-600/20 transition-transform duration-200 group-hover:scale-110">
                                <FolderTree class="h-5 w-5 text-cyan-400" />
                            </div>
                            <div>
                                <span class="text-sm font-medium text-foreground">Categorías</span>
                                <p class="text-[10px] text-muted-foreground">Organizar productos</p>
                            </div>
                        </Link>
                        <Link v-if="teamSlug" :href="route('admin.catalogo.productos.index', teamSlug)" class="group flex items-center gap-3 rounded-xl border border-border/40 bg-muted/20 p-4 transition-all duration-200 hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-indigo-600/10 hover:border-blue-500/30 hover:shadow-sm hover:-translate-y-0.5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-600/20 transition-transform duration-200 group-hover:scale-110">
                                <Package class="h-5 w-5 text-blue-400" />
                            </div>
                            <div>
                                <span class="text-sm font-medium text-foreground">Productos</span>
                                <p class="text-[10px] text-muted-foreground">Catálogo digital</p>
                            </div>
                        </Link>
                        <Link v-if="teamSlug" :href="route('admin.catalogo.servicios.index', teamSlug)" class="group flex items-center gap-3 rounded-xl border border-border/40 bg-muted/20 p-4 transition-all duration-200 hover:bg-gradient-to-r hover:from-amber-500/10 hover:to-orange-600/10 hover:border-amber-500/30 hover:shadow-sm hover:-translate-y-0.5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-600/20 transition-transform duration-200 group-hover:scale-110">
                                <Wrench class="h-5 w-5 text-amber-400" />
                            </div>
                            <div>
                                <span class="text-sm font-medium text-foreground">Servicios</span>
                                <p class="text-[10px] text-muted-foreground">Servicios ofrecidos</p>
                            </div>
                        </Link>
                        <Link v-if="teamSlug" :href="route('admin.clientes.index', teamSlug)" class="group flex items-center gap-3 rounded-xl border border-border/40 bg-muted/20 p-4 transition-all duration-200 hover:bg-gradient-to-r hover:from-emerald-500/10 hover:to-teal-600/10 hover:border-emerald-500/30 hover:shadow-sm hover:-translate-y-0.5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-600/20 transition-transform duration-200 group-hover:scale-110">
                                <UserCircle class="h-5 w-5 text-emerald-400" />
                            </div>
                            <div>
                                <span class="text-sm font-medium text-foreground">Clientes</span>
                                <p class="text-[10px] text-muted-foreground">Base de datos</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </PageTransition>
    </AppLayout>
</template>
