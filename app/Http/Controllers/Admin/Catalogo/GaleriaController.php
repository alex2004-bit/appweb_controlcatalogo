<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalogo\StoreGaleriaRequest;
use App\Http\Requests\Admin\Catalogo\UpdateGaleriaRequest;
use App\Models\Galeria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GaleriaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Galeria::class);

        $search = $request->input('search');
        $tipo = $request->input('tipo');

        $imagenes = Galeria::when($search, fn ($q) => $q->where(function ($q) use ($search) {
            $q->where('titulo', 'like', "%{$search}%")
                ->orWhere('descripcion', 'like', "%{$search}%");
        }))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('admin/catalogo/galeria/Index', [
            'imagenes' => $imagenes,
            'filters' => ['search' => $search, 'tipo' => $tipo],
        ]);
    }

    public function store(StoreGaleriaRequest $request): RedirectResponse
    {
        $this->authorize('create', Galeria::class);

        $data = $request->validated();

        $data['imagen'] = $request->file('imagen')->store('galeria', 'public');
        $data['activo'] = $request->boolean('activo', true);

        Galeria::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Imagen agregada correctamente.']);

        return back();
    }

    public function update(UpdateGaleriaRequest $request, Galeria $galeria): RedirectResponse
    {
        $this->authorize('update', $galeria);

        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('galeria', 'public');
        }

        $data['activo'] = $request->boolean('activo', true);

        $galeria->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Imagen actualizada correctamente.']);

        return back();
    }

    public function destroy(Galeria $galeria): RedirectResponse
    {
        $this->authorize('delete', $galeria);

        $galeria->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Imagen eliminada correctamente.']);

        return back();
    }
}
