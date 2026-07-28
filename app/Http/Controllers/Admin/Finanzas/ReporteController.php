<?php

namespace App\Http\Controllers\Admin\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\Pago;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::allowIf(fn ($user) => $user->can('view_reportes'));

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $ingresosPorMes = collect(range(1, 12))->map(fn ($m) => [
            'mes' => $m,
            'mes_nombre' => Carbon::create()->month($m)->format('M'),
            'total' => (float) Pago::where('estado', 'completado')
                ->whereYear('fecha_pago', $year)
                ->whereMonth('fecha_pago', $m)
                ->sum('monto'),
        ]);

        $metodosPago = Pago::where('estado', 'completado')
            ->whereYear('fecha_pago', $year)
            ->whereMonth('fecha_pago', $month)
            ->selectRaw('metodo_pago, COUNT(*) as total_pagos, SUM(monto) as total_monto')
            ->groupBy('metodo_pago')
            ->get();

        $productosTop = Producto::withCount(['detalles' => function ($q) {
            $q->whereHas('pedido', fn ($q) => $q->where('estado', '!=', 'cancelado'));
        }])->orderByDesc('detalles_count')->take(5)->get();

        $cotizacionesPorEstado = Cotizacion::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        return Inertia::render('admin/finanzas/reportes/Index', [
            'year' => (int) $year,
            'month' => (int) $month,
            'ingresosPorMes' => $ingresosPorMes,
            'metodosPago' => $metodosPago,
            'productosTop' => $productosTop,
            'cotizacionesPorEstado' => $cotizacionesPorEstado,
            'resumen' => [
                'ingresos_mes' => (float) Pago::where('estado', 'completado')
                    ->whereYear('fecha_pago', $year)
                    ->whereMonth('fecha_pago', $month)
                    ->sum('monto'),
                'ingresos_anio' => (float) Pago::where('estado', 'completado')
                    ->whereYear('fecha_pago', $year)
                    ->sum('monto'),
                'total_pedidos' => Cotizacion::count(),
                'total_cobrado' => (float) Pago::where('estado', 'completado')->sum('monto'),
            ],
        ]);
    }
}
