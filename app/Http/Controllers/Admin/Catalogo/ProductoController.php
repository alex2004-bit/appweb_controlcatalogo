<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalogo\StoreProductoRequest;
use App\Http\Requests\Admin\Catalogo\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Galeria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            ->when($categoriaId, function ($query) use ($categoriaId): void {
                $categoryIds = Categoria::query()
                    ->where(fn ($categories) => $categories->whereKey($categoriaId)->orWhere('parent_id', $categoriaId))
                    ->pluck('id');

                $query->whereIn('categoria_id', $categoryIds);
            })
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

        DB::transaction(function () use ($request): void {
            $data = $request->safe()->except(['imagen', 'imagenes']);
            $files = $request->file('imagenes', []);
            $data = $this->applyPurchaseMarkup($data);

            if ($request->hasFile('imagen')) {
                $data['imagen'] = $request->file('imagen')->store('productos', 'public');
            } elseif ($files !== []) {
                $data['imagen'] = $files[0]->store('productos', 'public');
                array_shift($files);
            }

            $producto = Producto::create($data);

            foreach ($files as $file) {
                $this->storeProductImage($producto, $file);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto creado correctamente.']);

        return to_route('admin.catalogo.productos.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(Producto $producto): Response
    {
        $this->authorize('update', $producto);
        $producto->load('imagenes');

        return Inertia::render('admin/catalogo/productos/Edit', [
            'producto' => $producto,
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
            'marcas' => Marca::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->authorize('update', $producto);
        $imagePathsToDelete = [];

        DB::transaction(function () use ($request, $producto, &$imagePathsToDelete): void {
            $data = $request->safe()->except(['imagen', 'imagenes', 'eliminar_imagenes']);
            $data = $this->applyPurchaseMarkup($data, $producto);
            $oldImage = $producto->imagen;

            if ($request->hasFile('imagen')) {
                $data['imagen'] = $request->file('imagen')->store('productos', 'public');
            }

            $producto->update($data);

            foreach ($request->file('imagenes', []) as $file) {
                $this->storeProductImage($producto, $file);
            }

            $galleryImagesToDelete = $producto->imagenes()
                ->whereIn('id', $request->input('eliminar_imagenes', []))
                ->get();

            foreach ($galleryImagesToDelete as $galleryImage) {
                $imagePathsToDelete[] = $galleryImage->imagen;
                $galleryImage->delete();
            }

            if ($request->hasFile('imagen') && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
        });

        foreach (array_unique($imagePathsToDelete) as $imagePath) {
            $isImageStillInUse = Producto::query()->where('imagen', $imagePath)->exists()
                || Galeria::query()->where('imagen', $imagePath)->exists();

            if (! $isImageStillInUse && Storage::disk('public')->exists($imagePath)) {
                if (! Storage::disk('public')->delete($imagePath)) {
                    Log::warning('Unable to delete unused product gallery image.', [
                        'producto_id' => $producto->id,
                        'path' => $imagePath,
                    ]);
                }
            }
        }

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

    private function storeProductImage(Producto $producto, mixed $file): void
    {
        $path = $file->store('productos', 'public');

        $producto->imagenes()->create([
            'titulo' => $producto->nombre,
            'imagen' => $path,
            'tipo' => 'producto',
            'activo' => true,
        ]);
    }

    /**
     * Derive the regular sale price from the purchase price when one is provided.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPurchaseMarkup(array $data, ?Producto $producto = null): array
    {
        $purchasePrice = array_key_exists('precio_compra', $data)
            ? $data['precio_compra']
            : $producto?->precio_compra;

        if ($purchasePrice !== null && $purchasePrice !== '') {
            $data['precio'] = round((float) $purchasePrice * 1.4, 2);
        }

        return $data;
    }
}
