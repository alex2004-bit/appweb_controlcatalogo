<?php

namespace App\Http\Controllers\Admin\Pedidos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePedidoRequest;
use App\Http\Requests\Admin\UpdatePedidoRequest;
use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PedidoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pedido::class);

        $search = $request->input('search');
        $estado = $request->input('estado');

        $pedidos = Pedido::with('cliente', 'creador', 'detalles')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn ($q) => $q->where('nombre', 'like', "%{$search}%"));
            }))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/pedidos/Index', [
            'pedidos' => $pedidos,
            'filters' => ['search' => $search, 'estado' => $estado],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Pedido::class);

        return Inertia::render('admin/pedidos/Create', [
            'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'email']),
            'productos' => Producto::where('activo', true)->get(['id', 'nombre', 'precio', 'sku', 'imagen']),
            'servicios' => Servicio::where('activo', true)->get(['id', 'nombre', 'precio', 'imagen', 'duracion_estimada']),
        ]);
    }

    public function store(StorePedidoRequest $request): RedirectResponse
    {
        $this->authorize('create', Pedido::class);

        $data = $request->validated();

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];
            $subtotal += $itemSubtotal - $itemDescuento;
        }

        $impuesto = $subtotal * 0.18;
        $total = $subtotal + $impuesto;

        $codigo = 'PED-'.now()->format('Ymd').'-'.str_pad((Pedido::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        $pedido = Pedido::create([
            'codigo' => $codigo,
            'cliente_id' => $data['cliente_id'],
            'created_by' => $request->user()->id,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'descuento' => 0,
            'total' => $total,
            'estado' => ($data['pagado'] ?? false) ? 'completado' : 'pendiente',
            'metodo_pago' => $data['metodo_pago'] ?? null,
            'pagado' => $data['pagado'] ?? false,
            'notas' => $data['notas'] ?? null,
        ]);

        if ($pedido->pagado) {
            $pedido->update(['completado_at' => now()]);
        }

        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'item_type' => $item['item_type'] === 'producto' ? Producto::class : Servicio::class,
                'item_id' => $item['item_id'],
                'item_nombre' => $item['item_nombre'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'descuento' => $itemDescuento,
                'subtotal' => $itemSubtotal - $itemDescuento,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido creado correctamente.']);

        return to_route('admin.pedidos.show', ['current_team' => $request->user()->currentTeam, 'pedido' => $pedido->id]);
    }

    public function show(Pedido $pedido): Response
    {
        $this->authorize('view', $pedido);

        $pedido->load('cliente', 'creador', 'detalles');

        return Inertia::render('admin/pedidos/Show', [
            'pedido' => $pedido,
        ]);
    }

    public function edit(Pedido $pedido): Response
    {
        $this->authorize('update', $pedido);

        $pedido->load('cliente', 'detalles');

        return Inertia::render('admin/pedidos/Edit', [
            'pedido' => $pedido,
            'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'email']),
            'productos' => Producto::where('activo', true)->get(['id', 'nombre', 'precio', 'sku']),
            'servicios' => Servicio::where('activo', true)->get(['id', 'nombre', 'precio']),
        ]);
    }

    public function update(UpdatePedidoRequest $request, Pedido $pedido): RedirectResponse
    {
        $this->authorize('update', $pedido);

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
            'metodo_pago' => $data['metodo_pago'] ?? null,
            'pagado' => $data['pagado'] ?? false,
            'notas' => $data['notas'] ?? null,
        ];

        if ($data['estado'] === 'completado' && $pedido->estado !== 'completado') {
            $extra['completado_at'] = now();
        }

        if ($data['estado'] === 'cancelado' && $pedido->estado !== 'cancelado') {
            $extra['cancelado_at'] = now();
        }

        $pedido->update([
            'cliente_id' => $data['cliente_id'],
            'estado' => $data['estado'],
            ...$extra,
        ]);

        $pedido->detalles()->delete();

        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
            $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'item_type' => $item['item_type'] === 'producto' ? Producto::class : Servicio::class,
                'item_id' => $item['item_id'],
                'item_nombre' => $item['item_nombre'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'descuento' => $itemDescuento,
                'subtotal' => $itemSubtotal - $itemDescuento,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido actualizado correctamente.']);

        return to_route('admin.pedidos.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        $this->authorize('delete', $pedido);

        $pedido->detalles()->delete();
        $pedido->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido eliminado correctamente.']);

        return to_route('admin.pedidos.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
