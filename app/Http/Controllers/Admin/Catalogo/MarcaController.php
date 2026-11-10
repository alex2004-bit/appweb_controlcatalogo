<?php

namespace App\Http\Controllers\Admin\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('catalogo/marcas', 'public');
        }

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
            'slug' => 'nullable|string|max:180|unique:marcas,slug,'.$marca->id,
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = $marca->logo;
            $validated['logo'] = $request->file('logo')->store('catalogo/marcas', 'public');
            $marca->update($validated);

            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        } else {
            $marca->update($validated);
        }

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
