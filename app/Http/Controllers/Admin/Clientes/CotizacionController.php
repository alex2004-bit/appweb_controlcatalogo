<?php

namespace App\Http\Controllers\Admin\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Clientes\StoreCotizacionRequest;
use App\Http\Requests\Admin\Clientes\UpdateCotizacionRequest;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CotizacionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cotizacion::class);

        $search = $request->input('search');
        $estado = $request->input('estado');

        $cotizaciones = Cotizacion::with('cliente', 'creador', 'detalles')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn ($q) => $q->where('nombre', 'like', "%{$search}%"));
            }))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
            'filters' => ['search' => $search, 'estado' => $estado],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Cotizacion::class);

        return Inertia::render('admin/cotizaciones/Create', [
            'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'email']),
            'productos' => Producto::where('activo', true)->get(['id', 'nombre', 'precio', 'sku']),
            'servicios' => Servicio::where('activo', true)->get(['id', 'nombre', 'precio']),
        ]);
    }

    public function store(StoreCotizacionRequest $request): RedirectResponse
    {
        $this->authorize('create', Cotizacion::class);

        $data = $request->validated();

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];
            $subtotal += $itemSubtotal - $itemDescuento;
        }

        $impuesto = $subtotal * 0.18;
        $total = $subtotal + $impuesto;

        $codigo = 'COT-'.now()->format('Ymd').'-'.str_pad((Cotizacion::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        $cotizacion = Cotizacion::create([
            'codigo' => $codigo,
            'cliente_id' => $data['cliente_id'],
            'created_by' => $request->user()->id,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'descuento' => 0,
            'total' => $total,
            'estado' => 'borrador',
            'notas' => $data['notas'] ?? null,
            'condiciones' => $data['condiciones'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'validez_dias' => $data['validez_dias'] ?? 30,
        ]);

        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];

            DetalleCotizacion::create([
                'cotizacion_id' => $cotizacion->id,
                'item_type' => $item['item_type'] === 'producto' ? Producto::class : Servicio::class,
                'item_id' => $item['item_id'],
                'item_nombre' => $item['item_nombre'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'descuento' => $itemDescuento,
                'subtotal' => $itemSubtotal - $itemDescuento,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cotización creada correctamente.']);

        return to_route('admin.cotizaciones.show', ['current_team' => $request->user()->currentTeam, 'cotizacion' => $cotizacion->id]);
    }

    public function show(Cotizacion $cotizacion): Response
    {
        $this->authorize('view', $cotizacion);

        $cotizacion->load('cliente', 'creador', 'detalles', 'pagos');
        $cotizacion->append('saldo_pendiente', 'estado_pago');

        return Inertia::render('admin/cotizaciones/Show', [
            'cotizacion' => $cotizacion,
        ]);
    }

    public function edit(Cotizacion $cotizacion): Response
    {
        $this->authorize('update', $cotizacion);

        $cotizacion->load('cliente', 'detalles');

        return Inertia::render('admin/cotizaciones/Edit', [
            'cotizacion' => $cotizacion,
            'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'email']),
            'productos' => Producto::where('activo', true)->get(['id', 'nombre', 'precio', 'sku']),
            'servicios' => Servicio::where('activo', true)->get(['id', 'nombre', 'precio']),
        ]);
    }

    public function update(UpdateCotizacionRequest $request, Cotizacion $cotizacion): RedirectResponse
    {
        $this->authorize('update', $cotizacion);

        $data = $request->validated();

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];
            $subtotal += $itemSubtotal - $itemDescuento;
        }

        $impuesto = $subtotal * 0.18;
        $total = $subtotal + $impuesto;

        $extra = [
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
            'validez_dias' => $data['validez_dias'] ?? 30,
            'notas' => $data['notas'] ?? null,
            'condiciones' => $data['condiciones'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ];

        if ($data['estado'] === 'aprobada' && $cotizacion->estado !== 'aprobada') {
            $extra['aprobada_at'] = now();
        }

        if ($data['estado'] === 'rechazada' && $cotizacion->estado !== 'rechazada') {
            $extra['rechazada_at'] = now();
        }

        $cotizacion->update([
            'cliente_id' => $data['cliente_id'],
            'estado' => $data['estado'],
            ...$extra,
        ]);

        $cotizacion->detalles()->delete();

        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];

            DetalleCotizacion::create([
                'cotizacion_id' => $cotizacion->id,
                'item_type' => $item['item_type'] === 'producto' ? Producto::class : Servicio::class,
                'item_id' => $item['item_id'],
                'item_nombre' => $item['item_nombre'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'descuento' => $itemDescuento,
                'subtotal' => $itemSubtotal - $itemDescuento,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cotización actualizada correctamente.']);

        return to_route('admin.cotizaciones.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Cotizacion $cotizacion): RedirectResponse
    {
        $this->authorize('delete', $cotizacion);

        $cotizacion->detalles()->delete();
        $cotizacion->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cotización eliminada correctamente.']);

        return to_route('admin.cotizaciones.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
