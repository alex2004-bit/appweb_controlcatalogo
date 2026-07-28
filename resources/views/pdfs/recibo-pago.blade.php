<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Pago #{{ $pago->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.6; color: #1f2937; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .header h1 { font-size: 24px; margin: 0; color: #111827; }
        .header p { margin: 4px 0 0; color: #6b7280; font-size: 11px; }
        .recibo-info { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .recibo-info div { width: 48%; }
        .recibo-info h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin: 0 0 6px; }
        .recibo-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background: #f3f4f6; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #1f2937; border-bottom: none; padding-top: 10px; }
        .footer { margin-top: 30px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-completado { background: #d1fae5; color: #065f46; }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-rechazado { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $empresa }}</h1>
        <p>Recibo de Pago</p>
    </div>

    <div class="recibo-info">
        <div>
            <h3>Pago</h3>
            <p><strong>N° Recibo:</strong> {{ $pago->id }}</p>
            <p><strong>Fecha:</strong> {{ $pago->fecha_pago?->format('d/m/Y') }}</p>
            <p><strong>Método:</strong> {{ strtoupper($pago->metodo_pago) }}</p>
            <p><strong>Referencia:</strong> {{ $pago->referencia ?? '—' }}</p>
            <p><strong>Estado:</strong>
                <span class="badge badge-{{ $pago->estado }}">{{ strtoupper($pago->estado) }}</span>
            </p>
        </div>
        <div>
            <h3>Cotización</h3>
            <p><strong>Código:</strong> {{ $pago->cotizacion->codigo }}</p>
            <p><strong>Cliente:</strong> {{ $pago->cotizacion->cliente?->nombre ?? '—' }}</p>
            <p><strong>Documento:</strong> {{ $pago->cotizacion->cliente?->documento ?? '—' }}</p>
            <p><strong>Dirección:</strong> {{ $pago->cotizacion->cliente?->direccion ?? '—' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="text-align: right">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pago a Cotización {{ $pago->cotizacion->codigo }}</td>
                <td style="text-align: right">$ {{ number_format($pago->monto, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Pagado</td>
                <td style="text-align: right">$ {{ number_format($pago->monto, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($pago->notas)
        <div style="margin-bottom: 16px;">
            <h3 style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin: 0 0 6px;">Notas</h3>
            <p style="margin: 0;">{{ $pago->notas }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Este documento es un comprobante de pago generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
