<?php

namespace App\Http\Controllers\Admin\Pedidos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePedidoRequest;
use App\Http\Requests\Admin\UpdatePedidoRequest;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PedidoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pedido::class);

        $search = $request->input('search');
        $estado = $request->input('estado');

        $pedidos = Pedido::with('cliente', 'creador', 'vendedor', 'detalles')
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
            'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'email', 'telefono', 'direccion']),
            'vendedores' => User::whereNotNull('name')->orderBy('name')->get(['id', 'name']),
            'productos' => Producto::where('activo', true)->get(['id', 'nombre', 'precio', 'sku', 'imagen']),
            'servicios' => Servicio::where('activo', true)->get(['id', 'nombre', 'precio', 'imagen', 'duracion_estimada']),
        ]);
    }

    public function store(StorePedidoRequest $request): RedirectResponse
    {
        $this->authorize('create', Pedido::class);

        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $validatedItems = [];
            $subtotal = 0;

            // Agrupar items por item_id para validar stock total
            $aggregatedItems = $this->aggregateItems($data['items']);

            foreach ($aggregatedItems as $item) {
                $itemData = $this->resolveItemWithLock($item);
                if (! $itemData) {
                    throw ValidationException::withMessages([
                        'items' => 'Producto o servicio no encontrado o inactivo.',
                    ]);
                }

                if ($itemData['type'] === 'producto') {
                    $producto = $itemData['model'];
                    if ($producto->stock !== null && $producto->stock < $item['cantidad']) {
                        throw ValidationException::withMessages([
                            'items' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}, solicitado: {$item['cantidad']}.",
                        ]);
                    }
                }

                $itemSubtotal = $item['cantidad'] * $itemData['precio'];
                $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];
                $subtotal += $itemSubtotal - $itemDescuento;

                $validatedItems[] = [
                    'item_type' => $itemData['class'],
                    'item_id' => $itemData['model']->id,
                    'item_nombre' => $itemData['model']->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $itemData['precio'],
                    'descuento' => $itemDescuento,
                    'subtotal' => $itemSubtotal - $itemDescuento,
                ];
            }

            $impuesto = $subtotal * Configuracion::impuestoRate();
            $total = $subtotal + $impuesto;
            $anticipo = (float) ($data['anticipo'] ?? 0);

            // Create pedido first to get the auto-increment ID, then generate code
            $pedido = Pedido::create([
                'cliente_id' => $data['cliente_id'],
                'created_by' => $request->user()->id,
                'vendedor_id' => $data['vendedor_id'] ?? null,
                'referido_por' => $data['referido_por'] ?? null,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'descuento' => 0,
                'total' => $total,
                'anticipo' => $anticipo,
                'numero_cuotas' => $data['numero_cuotas'] ?? 1,
                'plazo_dias' => $data['plazo_dias'] ?? null,
                'estado' => ($data['pagado'] ?? false) ? 'completado' : 'pendiente',
                'metodo_pago' => $data['metodo_pago'] ?? null,
                'pagado' => $data['pagado'] ?? false,
                'notas' => $data['notas'] ?? null,
                'direccion_entrega' => $data['direccion_entrega'] ?? null,
                'ciudad' => $data['ciudad'] ?? null,
                'provincia' => $data['provincia'] ?? null,
                'tipo_entrega' => $data['tipo_entrega'] ?? 'tienda',
                'fecha_entrega' => $data['fecha_entrega'] ?? null,
                'telefono_contacto' => $data['telefono_contacto'] ?? null,
            ]);

            // Generate code using the actual ID
            $codigo = 'PED-'.now()->format('Ymd').'-'.str_pad($pedido->id, 4, '0', STR_PAD_LEFT);
            $pedido->update(['codigo' => $codigo]);

            if ($pedido->pagado) {
                $pedido->update(['completado_at' => now()]);
            }

            foreach ($validatedItems as $item) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'item_nombre' => $item['item_nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal'],
                ]);

                if ($item['item_type'] === Producto::class) {
                    Producto::where('id', $item['item_id'])->decrement('stock', $item['cantidad']);
                }
            }

            return to_route('admin.pedidos.show', ['current_team' => $request->user()->currentTeam, 'pedido' => $pedido->id]);
        });
    }

    private function resolveItemWithLock(array $item): ?array
    {
        if ($item['item_type'] === 'producto') {
            $model = Producto::where('id', $item['item_id'])
                ->where('activo', true)
                ->lockForUpdate()
                ->first();
            $class = Producto::class;
        } else {
            $model = Servicio::where('id', $item['item_id'])
                ->where('activo', true)
                ->first();
            $class = Servicio::class;
        }

        if (! $model) {
            return null;
        }

        return [
            'model' => $model,
            'class' => $class,
            'type' => $item['item_type'],
            'precio' => $model->precio,
        ];
    }

    public function show(Pedido $pedido): Response
    {
        $this->authorize('view', $pedido);

        $pedido->load('cliente', 'creador', 'vendedor', 'detalles');

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
            'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre', 'documento', 'email', 'telefono', 'direccion']),
            'vendedores' => User::whereNotNull('name')->orderBy('name')->get(['id', 'name']),
            'productos' => Producto::where('activo', true)->get(['id', 'nombre', 'precio', 'sku']),
            'servicios' => Servicio::where('activo', true)->get(['id', 'nombre', 'precio']),
        ]);
    }

    public function update(UpdatePedidoRequest $request, Pedido $pedido): RedirectResponse
    {
        $this->authorize('update', $pedido);

        $data = $request->validated();

        return DB::transaction(function () use ($data, $pedido, $request) {
            $validatedItems = [];
            $subtotal = 0;

            // Collect all product IDs that will be affected (old + new)
            $oldProductIds = $pedido->detalles
                ->where('item_type', Producto::class)
                ->whereNotNull('item_id')
                ->pluck('item_id')
                ->unique()
                ->values()
                ->all();

            $newProductIds = collect($data['items'])
                ->where('item_type', 'producto')
                ->pluck('item_id')
                ->unique()
                ->values()
                ->all();

            $allProductIds = array_unique(array_merge($oldProductIds, $newProductIds));

            // Lock all affected product rows at the start to prevent race conditions
            if (! empty($allProductIds)) {
                Producto::whereIn('id', $allProductIds)
                    ->lockForUpdate()
                    ->get();
            }

            // A cancelled order has already released its stock.
            $reserved = [];
            if ($pedido->estado !== 'cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->item_type === Producto::class && $detalle->item_id) {
                        $reserved[$detalle->item_id] = ($reserved[$detalle->item_id] ?? 0) + $detalle->cantidad;
                    }
                }
            }

            // Agrupar items por item_id para validar stock total
            $aggregatedItems = $this->aggregateItems($data['items']);

            foreach ($aggregatedItems as $item) {
                $itemData = $this->resolveItemWithLock($item);
                if (! $itemData) {
                    throw ValidationException::withMessages([
                        'items' => 'Producto o servicio no encontrado o inactivo.',
                    ]);
                }

                if ($itemData['type'] === 'producto') {
                    $producto = $itemData['model'];
                    if (($data['estado'] ?? null) !== 'cancelado') {
                        if ($producto->stock !== null) {
                            $available = $producto->stock + ($reserved[$producto->id] ?? 0);

                            if ($available < $item['cantidad']) {
                                throw ValidationException::withMessages([
                                    'items' => "Stock insuficiente para {$producto->nombre}. Disponible: {$available}, solicitado: {$item['cantidad']}.",
                                ]);
                            }
                        }
                    }
                }

                $itemSubtotal = $item['cantidad'] * $itemData['precio'];
                $itemDescuento = ($item['descuento'] ?? 0) * $item['cantidad'];
                $subtotal += $itemSubtotal - $itemDescuento;

                $validatedItems[] = [
                    'item_type' => $itemData['class'],
                    'item_id' => $itemData['model']->id,
                    'item_nombre' => $itemData['model']->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $itemData['precio'],
                    'descuento' => $itemDescuento,
                    'subtotal' => $itemSubtotal - $itemDescuento,
                ];
            }

            // Calculate stock adjustments: restore old, then apply new
            $stockAdjustments = [];

            // Restore old quantities
            foreach ($reserved as $productId => $oldQty) {
                $stockAdjustments[$productId] = ($stockAdjustments[$productId] ?? 0) + $oldQty;
            }

            // Apply new quantities (subtract)
            foreach ($aggregatedItems as $item) {
                if ($item['item_type'] === 'producto' && ($data['estado'] ?? null) !== 'cancelado') {
                    $stockAdjustments[$item['item_id']] = ($stockAdjustments[$item['item_id']] ?? 0) - $item['cantidad'];
                }
            }

            // Apply all stock adjustments atomically
            foreach ($stockAdjustments as $productId => $adjustment) {
                if ($adjustment !== 0) {
                    $producto = Producto::find($productId);
                    if ($producto && $producto->stock !== null) {
                        $newStock = $producto->stock + $adjustment;
                        if ($newStock < 0) {
                            throw ValidationException::withMessages([
                                'items' => "Stock resultante negativo para producto ID {$productId}.",
                            ]);
                        }
                        $producto->update(['stock' => $newStock]);
                    }
                }
            }

            $impuesto = $subtotal * Configuracion::impuestoRate();
            $total = $subtotal + $impuesto;

            $extra = [
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
                'vendedor_id' => $data['vendedor_id'] ?? null,
                'referido_por' => $data['referido_por'] ?? null,
                'anticipo' => $data['anticipo'] ?? 0,
                'numero_cuotas' => $data['numero_cuotas'] ?? 1,
                'plazo_dias' => $data['plazo_dias'] ?? null,
                'metodo_pago' => $data['metodo_pago'] ?? null,
                'pagado' => $data['pagado'] ?? false,
                'notas' => $data['notas'] ?? null,
                'direccion_entrega' => $data['direccion_entrega'] ?? null,
                'ciudad' => $data['ciudad'] ?? null,
                'provincia' => $data['provincia'] ?? null,
                'tipo_entrega' => $data['tipo_entrega'] ?? 'tienda',
                'fecha_entrega' => $data['fecha_entrega'] ?? null,
                'telefono_contacto' => $data['telefono_contacto'] ?? null,
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

            foreach ($validatedItems as $item) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'item_nombre' => $item['item_nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido actualizado correctamente.']);

            return to_route('admin.pedidos.index', ['current_team' => $request->user()->currentTeam]);
        });
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        $this->authorize('delete', $pedido);

        return DB::transaction(function () use ($pedido) {
            if ($pedido->estado !== 'cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->item_type === Producto::class && $detalle->item) {
                        $detalle->item->increment('stock', $detalle->cantidad);
                    }
                }
            }

            $pedido->detalles()->delete();
            $pedido->delete();

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido eliminado correctamente.']);

            return to_route('admin.pedidos.index', ['current_team' => auth()->user()->currentTeam]);
        });
    }

    /**
     * Agrupa items por item_id sumando sus cantidades.
     * Evita que el mismo producto en múltiples líneas supere el stock sin detectarlo.
     */
    private function aggregateItems(array $items): array
    {
        $aggregated = [];

        foreach ($items as $item) {
            $key = $item['item_type'].'_'.$item['item_id'];

            if (! isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'cantidad' => 0,
                    'descuento' => 0,
                ];
            }

            $aggregated[$key]['cantidad'] += (int) $item['cantidad'];
            $aggregated[$key]['descuento'] = max($aggregated[$key]['descuento'], (float) ($item['descuento'] ?? 0));
        }

        return array_values($aggregated);
    }
}
