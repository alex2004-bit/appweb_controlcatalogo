<?php

namespace App\Http\Controllers\Admin\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Clientes\UpdateConsultaRequest;
use App\Models\Consulta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsultaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Consulta::class);

        $search = $request->input('search');
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');

        $consultas = Consulta::with('cliente', 'leidoPor')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('empresa', 'like', "%{$search}%");
            }))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/consultas/Index', [
            'consultas' => $consultas,
            'filters' => ['search' => $search, 'estado' => $estado, 'tipo' => $tipo],
        ]);
    }

    public function show(Consulta $consulta): Response
    {
        $this->authorize('view', $consulta);

        $consulta->load('cliente', 'leidoPor');

        if (! $consulta->leido) {
            $consulta->update([
                'leido' => true,
                'leido_at' => now(),
                'leido_by' => auth()->id(),
            ]);
        }

        return Inertia::render('admin/consultas/Show', [
            'consulta' => $consulta,
        ]);
    }

    public function update(UpdateConsultaRequest $request, Consulta $consulta): RedirectResponse
    {
        $this->authorize('update', $consulta);

        $data = $request->validated();

        $consulta->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Consulta actualizada correctamente.']);

        return back();
    }

    public function destroy(Consulta $consulta): RedirectResponse
    {
        $this->authorize('delete', $consulta);

        $consulta->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Consulta eliminada correctamente.']);

        return to_route('admin.consultas.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
