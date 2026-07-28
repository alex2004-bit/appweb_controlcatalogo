<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalogo\StoreProductoRequest;
use App\Http\Requests\Admin\Catalogo\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Producto::class);

        $search = $request->input('search');
        $categoriaId = $request->input('categoria_id');
        $estado = $request->input('estado');

        $productos = Producto::with('categoria', 'marca')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->when($estado !== null, fn ($q) => $q->where('activo', $estado === 'activo'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/catalogo/productos/Index', [
            'productos' => $productos,
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
            'marcas' => Marca::where('activo', true)->get(['id', 'nombre']),
            'filters' => ['search' => $search, 'categoria_id' => $categoriaId, 'estado' => $estado],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Producto::class);

        return Inertia::render('admin/catalogo/productos/Create', [
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
            'marcas' => Marca::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $this->authorize('create', Producto::class);

        Producto::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto creado correctamente.']);

        return to_route('admin.catalogo.productos.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(Producto $producto): Response
    {
        $this->authorize('update', $producto);

        return Inertia::render('admin/catalogo/productos/Edit', [
            'producto' => $producto,
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
            'marcas' => Marca::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->authorize('update', $producto);

        $producto->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto actualizado correctamente.']);

        return to_route('admin.catalogo.productos.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorize('delete', $producto);

        $producto->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto eliminado correctamente.']);

        return to_route('admin.catalogo.productos.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
