<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Configuracion\UpdateSocialRequest;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SocialController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Configuracion::class);

        $config = Configuracion::firstOrCreate([]);

        return Inertia::render('admin/configuracion/Social', [
            'config' => $config,
        ]);
    }

    public function update(UpdateSocialRequest $request): RedirectResponse
    {
        $this->authorize('update', Configuracion::class);

        $data = $request->validated();

        $config = Configuracion::firstOrCreate([]);
        $config->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Redes sociales actualizadas correctamente.']);

        return back();
    }
}
