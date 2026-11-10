<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalogo\StoreCategoriaRequest;
use App\Http\Requests\Admin\Catalogo\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Categoria::class);

        $search = $request->input('search');
        $estado = $request->input('estado');

        $categorias = Categoria::with('parent')
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->when($estado !== null, fn ($q) => $q->where('activo', $estado === 'activo'))
            ->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('parent_id')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/catalogo/categorias/Index', [
            'categorias' => $categorias,
            'filters' => ['search' => $search, 'estado' => $estado],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Categoria::class);

        $categoriasPadre = Categoria::whereNull('parent_id')->where('activo', true)->get();

        return Inertia::render('admin/catalogo/categorias/Create', [
            'categoriasPadre' => $categoriasPadre,
        ]);
    }

    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        $this->authorize('create', Categoria::class);

        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('catalogo/categorias', 'public');
        }

        Categoria::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría creada correctamente.']);

        return to_route('admin.catalogo.categorias.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(Categoria $categoria): Response
    {
        $this->authorize('update', $categoria);

        $categoriasPadre = Categoria::whereNull('parent_id')
            ->where('id', '!=', $categoria->id)
            ->where('activo', true)
            ->get();

        return Inertia::render('admin/catalogo/categorias/Edit', [
            'categoria' => $categoria,
            'categoriasPadre' => $categoriasPadre,
        ]);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $this->authorize('update', $categoria);

        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $oldImage = $categoria->imagen;
            $data['imagen'] = $request->file('imagen')->store('catalogo/categorias', 'public');
            $categoria->update($data);

            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
        } else {
            $categoria->update($data);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría actualizada correctamente.']);

        return to_route('admin.catalogo.categorias.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $this->authorize('delete', $categoria);

        if ($categoria->children()->count() > 0) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No puedes eliminar una categoría con subcategorías.']);

            return back();
        }

        $categoria->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría eliminada correctamente.']);

        return to_route('admin.catalogo.categorias.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
