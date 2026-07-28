<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Configuracion\UpdateEmpresaRequest;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmpresaController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Configuracion::class);

        $config = Configuracion::firstOrCreate([], [
            'nombre_empresa' => 'Mi Empresa',
            'ruc' => '',
            'direccion' => '',
            'telefono' => '',
            'email' => '',
            'website' => '',
        ]);

        return Inertia::render('admin/configuracion/Empresa', [
            'config' => $config,
        ]);
    }

    public function update(UpdateEmpresaRequest $request): RedirectResponse
    {
        $this->authorize('update', Configuracion::class);

        $data = $request->validated();

        $config = Configuracion::firstOrCreate([]);
        $config->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Configuración actualizada correctamente.']);

        return back();
    }
}
