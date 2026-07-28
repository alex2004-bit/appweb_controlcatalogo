<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarcaController extends Controller
{
    public function index(): Response
    {
        $marcas = Marca::withCount('productos')
            ->latest()
            ->get();

        return Inertia::render('admin/catalogo/marcas/Index', [
            'marcas' => $marcas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/catalogo/marcas/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'slug' => 'nullable|string|max:180|unique:marcas,slug',
            'logo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        Marca::create($validated);

        return to_route('admin.catalogo.marcas.index')
            ->with('success', 'Marca creada correctamente.');
    }

    public function edit(Marca $marca): Response
    {
        return Inertia::render('admin/catalogo/marcas/Edit', [
            'marca' => $marca,
        ]);
    }

    public function update(Request $request, Marca $marca): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'slug' => 'nullable|string|max:180|unique:marcas,slug,' . $marca->id,
            'logo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $marca->update($validated);

        return to_route('admin.catalogo.marcas.index')
            ->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Marca $marca): RedirectResponse
    {
        $marca->delete();

        return to_route('admin.catalogo.marcas.index')
            ->with('success', 'Marca eliminada correctamente.');
    }
}
