<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Consulta;
use App\Models\Cotizacion;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Servicio;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $ingresosMes = Pago::where('estado', 'completado')
            ->whereMonth('fecha_pago', now()->month)
            ->whereYear('fecha_pago', now()->year)
            ->sum('monto');

        $pagosPendientes = Cotizacion::where('estado', '!=', 'rechazada')
            ->get()
            ->sum(fn ($c) => $c->saldo_pendiente);

        return Inertia::render('admin/dashboard/Index', [
            'stats' => [
                'categorias' => Categoria::count(),
                'productos' => Producto::count(),
                'servicios' => Servicio::count(),
                'consultas' => Consulta::count(),
                'cotizaciones' => Cotizacion::count(),
                'pagos' => Pago::where('estado', 'completado')->count(),
                'ingresos_mes' => (float) $ingresosMes,
                'pagos_pendientes' => (float) $pagosPendientes,
            ],
            'ultimasConsultas' => Consulta::with('cliente')
                ->latest()
                ->take(5)
                ->get(),
            'ultimasCotizaciones' => Cotizacion::with('cliente', 'creador')
                ->latest()
                ->take(5)
                ->get(),
            'ultimosPagos' => Pago::with('cotizacion.cliente')
                ->where('estado', 'completado')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
