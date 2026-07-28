<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Configuracion\UpdateGeneralesRequest;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GeneralesController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Configuracion::class);

        $config = Configuracion::firstOrCreate([]);

        return Inertia::render('admin/configuracion/Generales', [
            'config' => $config,
        ]);
    }

    public function update(UpdateGeneralesRequest $request): RedirectResponse
    {
        $this->authorize('update', Configuracion::class);

        $data = $request->validated();

        $config = Configuracion::firstOrCreate([]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('configuracion', 'public');
        }

        $config->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Configuración general actualizada correctamente.']);

        return back();
    }
}
