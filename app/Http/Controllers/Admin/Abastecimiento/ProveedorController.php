<?php

namespace App\Http\Controllers\Admin\Abastecimiento;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Abastecimiento\StoreProveedorRequest;
use App\Http\Requests\Admin\Abastecimiento\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Proveedor::class);

        $search = $request->string('search')->trim()->toString();
        $estado = $request->string('estado')->toString();

        $proveedores = Proveedor::query()
            ->withCount('compras')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$search}%")
                    ->orWhere('contacto', 'like', "%{$search}%");
            }))
            ->when(in_array($estado, ['activo', 'inactivo'], true), fn ($query) => $query->where('activo', $estado === 'activo'))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/abastecimiento/proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => ['search' => $search, 'estado' => $estado],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Proveedor::class);

        return Inertia::render('admin/abastecimiento/proveedores/Form', [
            'proveedor' => null,
        ]);
    }

    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        $this->authorize('create', Proveedor::class);

        Proveedor::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proveedor creado correctamente.']);

        return to_route('admin.abastecimiento.proveedores.index');
    }

    public function edit(Proveedor $proveedor): Response
    {
        $this->authorize('update', $proveedor);

        return Inertia::render('admin/abastecimiento/proveedores/Form', [
            'proveedor' => $proveedor,
        ]);
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $this->authorize('update', $proveedor);
        $proveedor->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proveedor actualizado correctamente.']);

        return to_route('admin.abastecimiento.proveedores.index');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $this->authorize('delete', $proveedor);
        $proveedor->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proveedor archivado correctamente.']);

        return to_route('admin.abastecimiento.proveedores.index');
    }
}
