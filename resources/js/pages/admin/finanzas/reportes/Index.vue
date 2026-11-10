<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BarChart3, DollarSign, ShoppingCart, TrendingUp, CreditCard, Package, FileText } from '@lucide/vue';
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Filler,
} from 'chart.js';
import { computed } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { ModuleHeader, StatsGrid } from '@/components/shared';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useMoney } from '@/composables/useMoney';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Filler);

const props = defineProps<{
    year: number; month: number;
    ingresosPorMes: Array<{ mes: number; mes_nombre: string; total: number }>;
    metodosPago: Array<{ metodo_pago: string; total_pagos: number; total_monto: number }>;
    productosTop: Array<{ id: number; nombre: string; detalles_count: number }>;
    cotizacionesPorEstado: Array<{ estado: string; total: number }>;
    resumen: { ingresos_mes: number; ingresos_anio: number; total_pedidos: number; total_cobrado: number };
}>();

const metodoPagoLabels: Record<string, string> = { efectivo: 'Efectivo', transferencia: 'Transferencia', tarjeta: 'Tarjeta', cheque: 'Cheque', deposito: 'Depósito', otro: 'Otro' };
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const { format, symbol } = useMoney();

function applyFilter(key: string, value: string) {
    router.get(route('admin.finanzas.reportes.index'), { year: props.year, month: props.month, [key]: value }, { preserveState: true, replace: true });
}

const statCards = computed(() => [
    { label: 'Ingresos del Mes', value: format(props.resumen.ingresos_mes), icon: DollarSign, color: 'bg-blue-600' },
    { label: 'Ingresos del Año', value: format(props.resumen.ingresos_anio), icon: TrendingUp, color: 'bg-emerald-600' },
    { label: 'Total Cotizaciones', value: props.resumen.total_pedidos, icon: ShoppingCart, color: 'bg-purple-600' },
    { label: 'Total Cobrado', value: format(props.resumen.total_cobrado), icon: CreditCard, color: 'bg-amber-600' },
]);

const barChartData = computed(() => ({
    labels: props.ingresosPorMes.map(i => i.mes_nombre),
    datasets: [{
        label: 'Ingresos',
        data: props.ingresosPorMes.map(i => i.total),
        backgroundColor: 'rgba(37, 99, 235, 0.7)',
        hoverBackgroundColor: 'rgba(37, 99, 235, 0.9)',
        borderRadius: 8,
        borderSkipped: false,
    }],
}));

const barOptions = computed(() => ({
    responsive: true, maintainAspectRatio: false,
    animation: { duration: 800, easing: 'easeOutQuart' as const },
    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, callbacks: { label: (ctx: any) => `${symbol.value} ${ctx.parsed.y.toFixed(2)}` } } },
    scales: { x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } }, y: { grid: { color: 'rgba(148, 163, 184, 0.15)' }, ticks: { color: '#94a3b8', callback: (v: any) => symbol.value + ' ' + v } } },
}));

const doughnutData = computed(() => ({
    labels: props.metodosPago.map(m => metodoPagoLabels[m.metodo_pago] || m.metodo_pago),
    datasets: [{ data: props.metodosPago.map(m => m.total_monto), backgroundColor: ['#2563eb', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899'], borderWidth: 0, hoverOffset: 8 }],
}));

const doughnutOptions = computed(() => ({ responsive: true, maintainAspectRatio: false, animation: { duration: 800 }, plugins: { legend: { position: 'bottom' as const, labels: { color: '#64748b', font: { size: 11 }, padding: 12, usePointStyle: true } }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, callbacks: { label: (ctx: any) => `${ctx.label}: ${symbol.value} ${ctx.parsed.toFixed(2)}` } } } }));
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Reportes" description="Estadísticas y análisis de ventas" :icon="BarChart3">
            <template #actions>
                <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                    <Select :model-value="String(month)" @update:model-value="(v) => applyFilter('month', String(v))">
                        <SelectTrigger class="h-10 w-full rounded-lg border-gray-300 bg-white sm:w-[130px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="m in 12" :key="m" :value="String(m)">{{ new Date(0, m - 1).toLocaleString('es', { month: 'long' }) }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select :model-value="String(year)" @update:model-value="(v) => applyFilter('year', String(v))">
                        <SelectTrigger class="h-10 w-full rounded-lg border-gray-300 bg-white sm:w-[100px]"><SelectValue /></SelectTrigger>
                        <SelectContent><SelectItem v-for="y in years" :key="y" :value="String(y)">{{ y }}</SelectItem></SelectContent>
                    </Select>
                </div>
            </template>
        </ModuleHeader>

        <StatsGrid :stats="statCards" :columns="4" />

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                        <TrendingUp class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Ingresos Mensuales</h2>
                        <p class="text-xs text-gray-500">Ingresos por mes</p>
                    </div>
                </div>
                <div class="h-72">
                    <Bar v-if="ingresosPorMes.some(i => i.total > 0)" :data="barChartData" :options="barOptions" />
                    <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">Sin datos para este período</div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                        <CreditCard class="h-5 w-5 text-purple-600" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Métodos de Pago</h2>
                        <p class="text-xs text-gray-500">Distribución por método</p>
                    </div>
                </div>
                <div class="h-72">
                    <Doughnut v-if="metodosPago.length" :data="doughnutData" :options="doughnutOptions" />
                    <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">Sin datos para este período</div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                        <Package class="h-5 w-5 text-emerald-600" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Productos Más Vendidos</h2>
                        <p class="text-xs text-gray-500">Top de productos</p>
                    </div>
                </div>
                <div v-if="productosTop.length" class="space-y-1">
                    <div v-for="(p, i) in productosTop" :key="p.id" class="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-600">{{ i + 1 }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ p.nombre }}</span>
                        </div>
                        <span class="text-sm font-semibold text-emerald-600">{{ p.detalles_count }} vendidos</span>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-gray-500">Sin datos disponibles</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                        <FileText class="h-5 w-5 text-amber-600" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Cotizaciones por Estado</h2>
                        <p class="text-xs text-gray-500">Resumen por estado</p>
                    </div>
                </div>
                <div v-if="cotizacionesPorEstado.length" class="space-y-2">
                    <div v-for="ce in cotizacionesPorEstado" :key="ce.estado" class="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full" :class="{ 'bg-gray-400': ce.estado === 'borrador', 'bg-blue-400': ce.estado === 'enviada', 'bg-emerald-400': ce.estado === 'aprobada', 'bg-red-400': ce.estado === 'rechazada' }" />
                            <span class="text-sm font-medium capitalize text-gray-900">{{ ce.estado }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ ce.total }}</span>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-gray-500">Sin cotizaciones registradas</p>
            </div>
        </div>
    </div>
</template>
