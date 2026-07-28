<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BarChart3, DollarSign, ShoppingCart, TrendingUp, CreditCard, Package, FileText, ArrowUp, ArrowDown } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PageTransition, ModuleHeader } from '@/components/shared';
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Filler,
} from 'chart.js';
import { Bar, Doughnut } from 'vue-chartjs';

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

function applyFilter(key: string, value: string) {
    router.get(route('admin.finanzas.reportes.index'), { year: props.year, month: props.month, [key]: value }, { preserveState: true, replace: true });
}

const barChartData = computed(() => ({
    labels: props.ingresosPorMes.map(i => i.mes_nombre),
    datasets: [{
        label: 'Ingresos',
        data: props.ingresosPorMes.map(i => i.total),
        backgroundColor: 'rgba(6, 182, 212, 0.7)',
        hoverBackgroundColor: 'rgba(6, 182, 212, 0.9)',
        borderRadius: 8,
        borderSkipped: false,
    }],
}));

const barOptions = {
    responsive: true, maintainAspectRatio: false,
    animation: { duration: 800, easing: 'easeOutQuart' as const },
    plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.9)', padding: 12, cornerRadius: 10, callbacks: { label: (ctx: any) => `$${ctx.parsed.y.toFixed(2)}` } } },
    scales: { x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }, y: { grid: { color: 'rgba(148, 163, 184, 0.08)' }, ticks: { color: '#64748b', callback: (v: any) => '$' + v } } },
};

const doughnutData = computed(() => ({
    labels: props.metodosPago.map(m => metodoPagoLabels[m.metodo_pago] || m.metodo_pago),
    datasets: [{ data: props.metodosPago.map(m => m.total_monto), backgroundColor: ['#06b6d4', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899'], borderWidth: 0, hoverOffset: 8 }],
}));

const doughnutOptions = { responsive: true, maintainAspectRatio: false, animation: { duration: 800 }, plugins: { legend: { position: 'bottom' as const, labels: { color: '#94a3b8', font: { size: 11 }, padding: 12, usePointStyle: true } }, tooltip: { backgroundColor: 'rgba(0,0,0,0.9)', padding: 12, cornerRadius: 10, callbacks: { label: (ctx: any) => `${ctx.label}: $${ctx.parsed.toFixed(2)}` } } } };
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Reportes" description="Estadísticas y análisis de ventas" :icon="BarChart3" gradient="from-cyan-500 to-blue-600">
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Select :model-value="String(month)" @update:model-value="(v: string) => applyFilter('month', v)">
                            <SelectTrigger class="h-10 rounded-xl w-[130px] bg-background border-input"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="m in 12" :key="m" :value="String(m)">{{ new Date(0, m - 1).toLocaleString('es', { month: 'long' }) }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select :model-value="String(year)" @update:model-value="(v: string) => applyFilter('year', v)">
                            <SelectTrigger class="h-10 rounded-xl w-[100px] bg-background border-input"><SelectValue /></SelectTrigger>
                            <SelectContent><SelectItem v-for="y in years" :key="y" :value="String(y)">{{ y }}</SelectItem></SelectContent>
                        </Select>
                    </div>
                </template>
            </ModuleHeader>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(item, i) in [
                    { label: 'Ingresos del Mes', value: `$${resumen.ingresos_mes.toFixed(2)}`, icon: DollarSign, color: 'from-cyan-500 to-blue-600', bg: 'from-cyan-500/10 to-blue-600/10', trend: 'up' },
                    { label: 'Ingresos del Año', value: `$${resumen.ingresos_anio.toFixed(2)}`, icon: TrendingUp, color: 'from-emerald-500 to-green-600', bg: 'from-emerald-500/10 to-green-600/10', trend: 'up' },
                    { label: 'Total Cotizaciones', value: resumen.total_pedidos, icon: ShoppingCart, color: 'from-purple-500 to-pink-600', bg: 'from-purple-500/10 to-pink-600/10', trend: 'neutral' },
                    { label: 'Total Cobrado', value: `$${resumen.total_cobrado.toFixed(2)}`, icon: CreditCard, color: 'from-amber-500 to-orange-600', bg: 'from-amber-500/10 to-orange-600/10', trend: 'up' },
                ]" :key="i" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                    <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-5" :class="item.bg" />
                    <div class="h-1 w-full bg-gradient-to-r rounded-t-2xl" :class="item.color" />
                    <div class="mt-3 flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ item.label }}</p>
                            <p class="mt-2 text-2xl font-bold tracking-tight text-foreground">{{ item.value }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl shadow-sm transition-transform duration-300 group-hover:scale-110" :class="'bg-gradient-to-br ' + item.color">
                            <component :is="item.icon" class="h-5 w-5 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <Card class="border-border/50 shadow-sm overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-cyan-500 to-blue-600" />
                    <CardHeader><CardTitle class="text-lg font-semibold flex items-center gap-2"><TrendingUp class="h-5 w-5 text-cyan-400" /> Ingresos Mensuales</CardTitle></CardHeader>
                    <CardContent>
                        <div class="h-72">
                            <Bar v-if="ingresosPorMes.some(i => i.total > 0)" :data="barChartData" :options="barOptions" />
                            <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">Sin datos para este período</div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border/50 shadow-sm overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-purple-500 to-pink-600" />
                    <CardHeader><CardTitle class="text-lg font-semibold flex items-center gap-2"><CreditCard class="h-5 w-5 text-purple-400" /> Métodos de Pago</CardTitle></CardHeader>
                    <CardContent>
                        <div class="h-72">
                            <Doughnut v-if="metodosPago.length" :data="doughnutData" :options="doughnutOptions" />
                            <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">Sin datos para este período</div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border/50 shadow-sm overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-emerald-500 to-green-600" />
                    <CardHeader><CardTitle class="text-lg font-semibold flex items-center gap-2"><Package class="h-5 w-5 text-emerald-400" /> Productos Más Vendidos</CardTitle></CardHeader>
                    <CardContent>
                        <div v-if="productosTop.length" class="space-y-1">
                            <div v-for="(p, i) in productosTop" :key="p.id" class="flex items-center justify-between rounded-xl p-3 transition-colors hover:bg-accent/50">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500/20 to-green-600/20 text-xs font-bold text-emerald-400">{{ i + 1 }}</span>
                                    <span class="text-sm font-medium text-foreground">{{ p.nombre }}</span>
                                </div>
                                <span class="text-sm font-semibold text-emerald-400">{{ p.detalles_count }} vendidos</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground py-8 text-center">Sin datos disponibles</p>
                    </CardContent>
                </Card>

                <Card class="border-border/50 shadow-sm overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-600" />
                    <CardHeader><CardTitle class="text-lg font-semibold flex items-center gap-2"><FileText class="h-5 w-5 text-amber-400" /> Cotizaciones por Estado</CardTitle></CardHeader>
                    <CardContent>
                        <div v-if="cotizacionesPorEstado.length" class="space-y-2">
                            <div v-for="ce in cotizacionesPorEstado" :key="ce.estado" class="flex items-center justify-between rounded-xl p-3 transition-colors hover:bg-accent/50">
                                <div class="flex items-center gap-3">
                                    <div class="h-3 w-3 rounded-full" :class="{ 'bg-gray-400': ce.estado === 'borrador', 'bg-blue-400': ce.estado === 'enviada', 'bg-emerald-400': ce.estado === 'aprobada', 'bg-red-400': ce.estado === 'rechazada' }" />
                                    <span class="text-sm font-medium capitalize text-foreground">{{ ce.estado }}</span>
                                </div>
                                <span class="text-sm font-bold text-foreground">{{ ce.total }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground py-8 text-center">Sin cotizaciones registradas</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PageTransition>
</template>
