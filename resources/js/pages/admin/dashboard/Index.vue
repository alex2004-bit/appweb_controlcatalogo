<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    ArrowRight,
    Users,
    Package,
    MessageSquare,
    FileText,
    CreditCard,
    DollarSign,
    TrendingUp,
    Clock,
    FolderTree,
    UserCircle,
    BarChart3,
    Zap,
    Wrench,
} from '@lucide/vue';
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
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { ModuleHeader, StatsGrid } from '@/components/shared';
import { useMoney } from '@/composables/useMoney';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Filler);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentTeam = computed(() => page.props.currentTeam);
const teamSlug = computed(() => currentTeam?.value?.slug ?? '');
const { symbol, format } = useMoney();

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
    { label: 'Categorías', value: props.stats.categorias, icon: FolderTree, color: 'bg-blue-600' },
    { label: 'Productos', value: props.stats.productos, icon: Package, color: 'bg-indigo-600' },
    { label: 'Clientes', value: props.stats.total_clientes ?? 0, icon: Users, color: 'bg-emerald-600' },
    { label: 'Cotizaciones', value: props.stats.cotizaciones, icon: FileText, color: 'bg-teal-600' },
    { label: 'Consultas', value: props.stats.consultas, icon: MessageSquare, color: 'bg-purple-600' },
    { label: 'Pagos', value: props.stats.pagos, icon: CreditCard, color: 'bg-rose-600' },
    { label: 'Ingresos del Mes', value: format(props.stats.ingresos_mes || 0), icon: DollarSign, color: 'bg-green-600', sublabel: props.stats.ingresos_anio ? format(props.stats.ingresos_anio || 0) + ' año' : '' },
    { label: 'Por Cobrar', value: format(props.stats.pagos_pendientes || 0), icon: Clock, color: 'bg-orange-600' },
]);

const chartData = computed(() => ({
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    datasets: [{
        label: 'Ingresos',
        data: props.ingresosUltimos12
            ? props.ingresosUltimos12.map(i => i.total)
            : [0, 0, 0, 0, 0, props.stats.ingresos_mes || 0, 0, 0, 0, 0, 0, 0],
        borderColor: '#2563eb',
        backgroundColor: (ctx: any) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

            return gradient;
        },
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#2563eb',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: '#1d4ed8',
    }],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart' as const },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 12,
            cornerRadius: 8,
            titleFont: { size: 12 },
            bodyFont: { size: 13 },
            callbacks: {
                label: (ctx: any) => `${symbol.value} ${ctx.parsed.y.toFixed(2)}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } },
        },
        y: {
            grid: { color: 'rgba(148, 163, 184, 0.15)' },
            ticks: { color: '#94a3b8', font: { size: 11 }, callback: (v: any) => symbol.value + ' ' + v },
            beginAtZero: true,
        },
    },
    interaction: { intersect: false, mode: 'index' as const },
}));

const estadoColors: Record<string, string> = {
    pendiente: 'bg-yellow-100 text-yellow-800',
    en_proceso: 'bg-blue-100 text-blue-800',
    respondido: 'bg-green-100 text-green-800',
    cerrado: 'bg-gray-100 text-gray-600',
    borrador: 'bg-gray-100 text-gray-600',
    enviada: 'bg-blue-100 text-blue-800',
    aprobada: 'bg-green-100 text-green-800',
    rechazada: 'bg-red-100 text-red-800',
    completado: 'bg-green-100 text-green-800',
    parcial: 'bg-yellow-100 text-yellow-800',
    pagado: 'bg-green-100 text-green-800',
};

const metodoPagoLabels: Record<string, string> = {
    efectivo: 'Efectivo',
    transferencia: 'Transferencia',
    tarjeta: 'Tarjeta',
    cheque: 'Cheque',
    deposito: 'Depósito',
    otro: 'Otro',
};

function formatDate(dateStr: string) {
    if (!dateStr) {
return '';
}

    const d = new Date(dateStr);

    return d.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' });
}

function getInitials(name: string) {
    return name?.charAt(0).toUpperCase() ?? '?';
}
</script>

<template>
    <div class="flex flex-col gap-6">
            <ModuleHeader
                title="Panel de Control"
                :description="`Bienvenido, ${user?.name} — ${currentTeam?.name}`"
                :icon="LayoutGrid"
            >
                <template #actions>
                    <div class="flex items-center gap-2 rounded-lg bg-blue-50 border border-blue-200 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-green-500" />
                        <span class="text-xs font-medium text-blue-700">Sistema activo</span>
                    </div>
                </template>
            </ModuleHeader>

            <StatsGrid :stats="statCards" :columns="4" />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                                <TrendingUp class="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">Ingresos Anuales</h2>
                                <p class="text-xs text-gray-500">Evolución mensual de ingresos</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg bg-green-50 px-3 py-1.5">
                            <span class="h-2 w-2 rounded-full bg-green-500" />
                            <span class="text-xs font-semibold text-green-700">{{ format(props.stats.ingresos_anio || props.stats.ingresos_mes || 0) }}</span>
                        </div>
                    </div>
                    <div class="h-72">
                        <Line v-if="(props.ingresosUltimos12 || []).some(i => i.total > 0) || (props.stats.ingresos_mes || 0) > 0" :data="chartData" :options="chartOptions" />
                        <div v-else class="flex h-full items-center justify-center">
                            <div class="text-center">
                                <BarChart3 class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                <p class="text-sm text-gray-500">Aún no hay datos de ingresos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                                <MessageSquare class="h-5 w-5 text-purple-600" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">Consultas</h2>
                                <p class="text-xs text-gray-500">Últimas recibidas</p>
                            </div>
                        </div>
                        <Link v-if="teamSlug" :href="route('admin.consultas.index', teamSlug)" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 transition-colors">
                            Ver <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div class="space-y-2 max-h-80 overflow-y-auto scrollbar-thin">
                        <div v-for="consulta in ultimasConsultas.slice(0, 6)" :key="consulta.id" class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 transition-all hover:bg-gray-100">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-purple-600 text-xs font-bold text-white shadow-sm">
                                {{ getInitials(consulta.nombre) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ consulta.nombre }}</p>
                                    <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold" :class="estadoColors[consulta.estado] || 'bg-gray-100 text-gray-500'">
                                        {{ consulta.estado }}
                                    </span>
                                </div>
                                <p class="mt-0.5 line-clamp-1 text-xs text-gray-500">{{ consulta.mensaje }}</p>
                                <p class="mt-1 text-[10px] text-gray-400">{{ formatDate(consulta.created_at) }}</p>
                            </div>
                        </div>
                        <p v-if="!ultimasConsultas.length" class="py-8 text-center text-sm text-gray-500">No hay consultas recibidas.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-100">
                                <FileText class="h-5 w-5 text-teal-600" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">Cotizaciones</h2>
                                <p class="text-xs text-gray-500">Últimas emitidas</p>
                            </div>
                        </div>
                        <Link v-if="teamSlug" :href="route('admin.cotizaciones.index', teamSlug)" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 transition-colors">
                            Ver <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div class="space-y-2">
                        <div v-for="cot in ultimasCotizaciones.slice(0, 5)" :key="cot.id" class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 transition-all hover:bg-gray-100">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white shadow-sm">
                                {{ cot.codigo?.slice(-3) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ cot.cliente?.nombre ?? 'Sin cliente' }}</p>
                                    <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold" :class="estadoColors[cot.estado] || 'bg-gray-100 text-gray-500'">
                                        {{ cot.estado }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                                    <span class="font-mono text-[10px] bg-white px-1.5 py-0.5 rounded border border-gray-200">{{ cot.codigo }}</span>
                                    <span class="text-teal-600 font-semibold">{{ format(cot.total) }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="!ultimasCotizaciones.length" class="py-8 text-center text-sm text-gray-500">No hay cotizaciones creadas.</p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                <DollarSign class="h-5 w-5 text-green-600" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">Pagos</h2>
                                <p class="text-xs text-gray-500">Últimos registrados</p>
                            </div>
                        </div>
                        <Link v-if="teamSlug" :href="route('admin.finanzas.pagos.index', teamSlug)" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 transition-colors">
                            Ver <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div class="space-y-2">
                        <div v-for="pago in ultimosPagos.slice(0, 5)" :key="pago.id" class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 transition-all hover:bg-gray-100">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-600 text-white shadow-sm">
                                <DollarSign class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ pago.cotizacion?.cliente?.nombre ?? '—' }}</p>
                                    <span class="text-sm font-bold text-green-600">{{ format(pago.monto) }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                                    <span class="bg-white px-1.5 py-0.5 rounded font-mono text-[10px] border border-gray-200">{{ pago.cotizacion?.codigo ?? '—' }}</span>
                                    <span>{{ metodoPagoLabels[pago.metodo_pago] || pago.metodo_pago }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="!ultimosPagos.length" class="py-8 text-center text-sm text-gray-500">No hay pagos registrados.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                        <Zap class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Acceso Rápido</h2>
                        <p class="text-xs text-gray-500">Módulos más utilizados</p>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Link v-if="teamSlug" :href="route('admin.catalogo.categorias.index', teamSlug)" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 transition-all hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-0.5">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-100">
                            <FolderTree class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900">Categorías</span>
                            <p class="text-[10px] text-gray-500">Organizar productos</p>
                        </div>
                    </Link>
                    <Link v-if="teamSlug" :href="route('admin.catalogo.productos.index', teamSlug)" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 transition-all hover:border-indigo-300 hover:bg-indigo-50 hover:-translate-y-0.5">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100">
                            <Package class="h-5 w-5 text-indigo-600" />
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900">Productos</span>
                            <p class="text-[10px] text-gray-500">Catálogo digital</p>
                        </div>
                    </Link>
                    <Link v-if="teamSlug" :href="route('admin.catalogo.servicios.index', teamSlug)" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 transition-all hover:border-amber-300 hover:bg-amber-50 hover:-translate-y-0.5">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-100">
                            <Wrench class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900">Servicios</span>
                            <p class="text-[10px] text-gray-500">Servicios ofrecidos</p>
                        </div>
                    </Link>
                    <Link v-if="teamSlug" :href="route('admin.clientes.index', teamSlug)" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 transition-all hover:border-emerald-300 hover:bg-emerald-50 hover:-translate-y-0.5">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-100">
                            <UserCircle class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900">Clientes</span>
                            <p class="text-[10px] text-gray-500">Base de datos</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
</template>
