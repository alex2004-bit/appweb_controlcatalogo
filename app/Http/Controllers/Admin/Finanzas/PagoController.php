<?php

namespace App\Http\Controllers\Admin\Finanzas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finanzas\StorePagoRequest;
use App\Http\Requests\Finanzas\UpdatePagoRequest;
use App\Models\Cotizacion;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PagoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pago::class);

        $search = $request->input('search');
        $metodoPago = $request->input('metodo_pago');
        $estado = $request->input('estado');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $pagos = Pago::with('cotizacion.cliente')
            ->when($search, fn ($q) => $q->whereHas('cotizacion.cliente', fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
                ->orWhere('referencia', 'like', "%{$search}%"))
            ->when($metodoPago, fn ($q) => $q->where('metodo_pago', $metodoPago))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($fechaDesde, fn ($q) => $q->whereDate('fecha_pago', '>=', $fechaDesde))
            ->when($fechaHasta, fn ($q) => $q->whereDate('fecha_pago', '<=', $fechaHasta))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/finanzas/pagos/Index', [
            'pagos' => $pagos,
            'filters' => [
                'search' => $search,
                'metodo_pago' => $metodoPago,
                'estado' => $estado,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Pago::class);

        $cotizaciones = Cotizacion::with('cliente')
            ->whereIn('estado', ['aprobada', 'enviada'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'cliente_nombre' => $c->cliente?->nombre ?? '—',
                'total' => $c->total,
                'saldo_pendiente' => $c->saldo_pendiente,
            ]);

        return Inertia::render('admin/finanzas/pagos/Create', [
            'cotizaciones' => $cotizaciones,
        ]);
    }

    public function store(StorePagoRequest $request): RedirectResponse
    {
        $this->authorize('create', Pago::class);

        $data = $request->validated();

        $pago = DB::transaction(function () use ($data, $request): Pago {
            $cotizacion = Cotizacion::whereKey($data['cotizacion_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $saldoPendiente = $cotizacion->saldo_pendiente;

            if ((float) $data['monto'] > $saldoPendiente) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto del pago no puede exceder el saldo pendiente de $'.number_format($saldoPendiente, 2),
                ]);
            }

            if ($request->hasFile('comprobante')) {
                $data['comprobante'] = $request->file('comprobante')->store('pagos/comprobantes', 'public');
            }

            $data['estado'] = 'completado';

            return Pago::create($data);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pago registrado correctamente.']);

        return to_route('admin.finanzas.pagos.show', ['current_team' => $request->user()->currentTeam, 'pago' => $pago->id]);
    }

    public function show(Pago $pago): Response
    {
        $this->authorize('view', $pago);

        $pago->load('cotizacion.cliente', 'cotizacion.detalles');

        $esUltimoPago = Pago::where('cotizacion_id', $pago->cotizacion_id)
            ->latest()
            ->value('id') === $pago->id;

        return Inertia::render('admin/finanzas/pagos/Show', [
            'pago' => $pago,
            'esUltimoPago' => $esUltimoPago,
        ]);
    }

    public function edit(Pago $pago): Response
    {
        $this->authorize('update', $pago);

        $ultimoPago = Pago::where('cotizacion_id', $pago->cotizacion_id)
            ->latest()
            ->first();

        if ($ultimoPago->id !== $pago->id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Solo se puede editar el último pago registrado.']);

            return to_route('admin.finanzas.pagos.show', ['current_team' => auth()->user()->currentTeam, 'pago' => $pago->id]);
        }

        return Inertia::render('admin/finanzas/pagos/Edit', [
            'pago' => $pago->load('cotizacion.cliente'),
        ]);
    }

    public function update(UpdatePagoRequest $request, Pago $pago): RedirectResponse
    {
        $this->authorize('update', $pago);

        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $pago): void {
            $cotizacion = Cotizacion::whereKey($pago->cotizacion_id)
                ->lockForUpdate()
                ->firstOrFail();
            $ultimoPago = Pago::where('cotizacion_id', $pago->cotizacion_id)
                ->latest()
                ->first();

            if (! $ultimoPago || $ultimoPago->id !== $pago->id) {
                throw ValidationException::withMessages([
                    'pago' => 'Solo se puede editar el último pago registrado.',
                ]);
            }

            $totalPagadoSinEste = $cotizacion->pagos()
                ->where('id', '!=', $pago->id)
                ->sum('monto');
            $saldoDisponible = (float) $cotizacion->total - (float) $totalPagadoSinEste;

            if ((float) $data['monto'] > $saldoDisponible) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto del pago no puede exceder el saldo pendiente de $'.number_format($saldoDisponible, 2),
                ]);
            }

            if ($request->hasFile('comprobante')) {
                $data['comprobante'] = $request->file('comprobante')->store('pagos/comprobantes', 'public');
            }

            $pago->update($data);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pago actualizado correctamente.']);

        return to_route('admin.finanzas.pagos.show', ['current_team' => $request->user()->currentTeam, 'pago' => $pago->id]);

    }

    public function destroy(Pago $pago): RedirectResponse
    {
        $this->authorize('delete', $pago);

        $ultimoPago = Pago::where('cotizacion_id', $pago->cotizacion_id)
            ->latest()
            ->first();

        if ($ultimoPago->id !== $pago->id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Solo se puede eliminar el último pago registrado.']);

            return back();
        }

        $pago->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pago eliminado correctamente.']);

        return to_route('admin.finanzas.pagos.index', ['current_team' => auth()->user()->currentTeam]);
    }

    public function pdf(Pago $pago)
    {
        $pago->load('cotizacion.cliente', 'cotizacion.creador');

        $pdf = Pdf::loadView('pdfs.recibo-pago', [
            'pago' => $pago,
            'empresa' => config('app.name'),
        ]);

        return $pdf->download("recibo-pago-{$pago->id}.pdf");
    }
}
