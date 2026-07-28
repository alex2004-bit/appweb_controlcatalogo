<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalogo\StoreServicioRequest;
use App\Http\Requests\Admin\Catalogo\UpdateServicioRequest;
use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServicioController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Servicio::class);

        $search = $request->input('search');
        $categoriaId = $request->input('categoria_id');
        $estado = $request->input('estado');

        $servicios = Servicio::with('categoria')
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->when($estado !== null, fn ($q) => $q->where('activo', $estado === 'activo'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/catalogo/servicios/Index', [
            'servicios' => $servicios,
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
            'filters' => ['search' => $search, 'categoria_id' => $categoriaId, 'estado' => $estado],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Servicio::class);

        return Inertia::render('admin/catalogo/servicios/Create', [
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreServicioRequest $request): RedirectResponse
    {
        $this->authorize('create', Servicio::class);

        Servicio::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Servicio creado correctamente.']);

        return to_route('admin.catalogo.servicios.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(Servicio $servicio): Response
    {
        $this->authorize('update', $servicio);

        return Inertia::render('admin/catalogo/servicios/Edit', [
            'servicio' => $servicio,
            'categorias' => Categoria::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $this->authorize('update', $servicio);

        $servicio->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Servicio actualizado correctamente.']);

        return to_route('admin.catalogo.servicios.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        $this->authorize('delete', $servicio);

        $servicio->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Servicio eliminado correctamente.']);

        return to_route('admin.catalogo.servicios.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
