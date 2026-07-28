<?php

namespace App\Http\Controllers\Admin\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Clientes\StoreClienteRequest;
use App\Http\Requests\Admin\Clientes\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cliente::class);

        $search = $request->input('search');
        $tipo = $request->input('tipo_persona');

        $clientes = Cliente::withCount('cotizaciones', 'consultas')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$search}%")
                    ->orWhere('empresa', 'like', "%{$search}%");
            }))
            ->when($tipo, fn ($q) => $q->where('tipo_persona', $tipo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/clientes/Index', [
            'clientes' => $clientes,
            'filters' => ['search' => $search, 'tipo_persona' => $tipo],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Cliente::class);

        return Inertia::render('admin/clientes/Create');
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $this->authorize('create', Cliente::class);

        $data = $request->validated();

        Cliente::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cliente creado correctamente.']);

        return to_route('admin.clientes.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(Cliente $cliente): Response
    {
        $this->authorize('update', $cliente);

        return Inertia::render('admin/clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->authorize('update', $cliente);

        $data = $request->validated();

        $cliente->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cliente actualizado correctamente.']);

        return to_route('admin.clientes.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $this->authorize('delete', $cliente);

        if ($cliente->cotizaciones()->count() > 0 || $cliente->consultas()->count() > 0) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar el cliente porque tiene cotizaciones o consultas asociadas.']);

            return back();
        }

        $cliente->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cliente eliminado correctamente.']);

        return to_route('admin.clientes.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
