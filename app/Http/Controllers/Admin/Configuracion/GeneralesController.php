<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Configuracion\UpdateGeneralesRequest;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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
        $oldBannerImages = [];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('configuracion', 'public');
        }

        if ($request->hasFile('qr_pago')) {
            $data['qr_pago'] = $request->file('qr_pago')->store('configuracion', 'public');
        }

        foreach (['banner_imagen_1', 'banner_imagen_2', 'banner_imagen_3'] as $field) {
            if ($request->hasFile($field)) {
                if ($config->{$field}) {
                    $oldBannerImages[] = $config->{$field};
                }

                $data[$field] = $request->file($field)->store('configuracion/banners', 'public');
            }
        }

        $config->update($data);

        if ($oldBannerImages !== []) {
            Storage::disk('public')->delete($oldBannerImages);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Configuración general actualizada correctamente.']);

        return back();
    }
}
