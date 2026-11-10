<?php

namespace App\Http\Controllers\Admin\Abastecimiento;

use App\Actions\Admin\Abastecimiento\RecibirCompra;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Abastecimiento\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CompraController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Compra::class);

        $search = $request->string('search')->trim()->toString();
        $estado = $request->string('estado')->toString();

        $compras = Compra::query()
            ->with('proveedor:id,nombre')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('codigo', 'like', "%{$search}%")
                    ->orWhere('numero_factura', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', fn ($providerQuery) => $providerQuery->where('nombre', 'like', "%{$search}%"));
            }))
            ->when(in_array($estado, ['pendiente', 'recibida', 'cancelada'], true), fn ($query) => $query->where('estado', $estado))
            ->latest('fecha_compra')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/abastecimiento/compras/Index', [
            'compras' => $compras,
            'filters' => ['search' => $search, 'estado' => $estado],
            'moneda' => Configuracion::moneda(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Compra::class);

        return Inertia::render('admin/abastecimiento/compras/Create', [
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'productos' => Producto::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'sku', 'precio_compra']),
            'impuestoRate' => Configuracion::impuestoRate(),
            'moneda' => Configuracion::moneda(),
        ]);
    }

    public function store(StoreCompraRequest $request): RedirectResponse
    {
        $this->authorize('create', Compra::class);
        $data = $request->validated();

        $compra = DB::transaction(function () use ($data, $request): Compra {
            $details = collect($data['items'])->map(function (array $item): array {
                $producto = Producto::query()->findOrFail($item['producto_id']);
                $subtotal = round($item['cantidad'] * $item['precio_unitario'], 2);

                return [
                    ...$item,
                    'producto_nombre' => $producto->nombre,
                    'subtotal' => $subtotal,
                ];
            });
            $subtotal = round($details->sum('subtotal'), 2);
            $impuesto = round($subtotal * Configuracion::impuestoRate(), 2);

            $compra = Compra::create([
                'codigo' => 'TMP-'.Str::ulid(),
                'proveedor_id' => $data['proveedor_id'],
                'created_by' => $request->user()->id,
                'fecha_compra' => $data['fecha_compra'],
                'numero_factura' => $data['numero_factura'] ?? null,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => round($subtotal + $impuesto, 2),
                'estado' => 'pendiente',
                'notas' => $data['notas'] ?? null,
            ]);
            $compra->update(['codigo' => 'C-'.now()->format('ymd').'-'.str_pad((string) $compra->id, 6, '0', STR_PAD_LEFT)]);
            $compra->detalles()->createMany($details->all());

            return $compra;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Compra registrada. Confirma la recepción cuando llegue el pedido.']);

        return to_route('admin.abastecimiento.compras.show', $compra);
    }

    public function show(Compra $compra): Response
    {
        $this->authorize('view', $compra);
        $compra->load(['proveedor', 'creador', 'detalles.producto']);

        return Inertia::render('admin/abastecimiento/compras/Show', [
            'compra' => $compra,
            'moneda' => Configuracion::moneda(),
        ]);
    }

    public function receive(RecibirCompra $recibirCompra, Compra $compra): RedirectResponse
    {
        $this->authorize('receive', $compra);
        $recibirCompra->execute($compra);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Recepción confirmada. El inventario y el costo de compra fueron actualizados.']);

        return to_route('admin.abastecimiento.compras.show', $compra);
    }

    public function cancel(Compra $compra): RedirectResponse
    {
        $this->authorize('cancel', $compra);

        DB::transaction(function () use ($compra): void {
            $compra = Compra::query()->lockForUpdate()->findOrFail($compra->id);

            if ($compra->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'compra' => 'Solo se pueden cancelar compras pendientes de recepción.',
                ]);
            }

            $compra->update([
                'estado' => 'cancelada',
                'cancelada_at' => now(),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Compra cancelada.']);

        return to_route('admin.abastecimiento.compras.show', $compra);
    }
}
