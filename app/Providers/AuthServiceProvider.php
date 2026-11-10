<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\Consulta;
use App\Models\Cotizacion;
use App\Models\Galeria;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Servicio;
use App\Policies\CategoriaPolicy;
use App\Policies\ClientePolicy;
use App\Policies\CompraPolicy;
use App\Policies\ConfiguracionPolicy;
use App\Policies\ConsultaPolicy;
use App\Policies\CotizacionPolicy;
use App\Policies\GaleriaPolicy;
use App\Policies\PagoPolicy;
use App\Policies\PedidoPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\ProveedorPolicy;
use App\Policies\ServicioPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Categoria::class => CategoriaPolicy::class,
        Producto::class => ProductoPolicy::class,
        Servicio::class => ServicioPolicy::class,
        Galeria::class => GaleriaPolicy::class,
        Cliente::class => ClientePolicy::class,
        Consulta::class => ConsultaPolicy::class,
        Proveedor::class => ProveedorPolicy::class,
        Compra::class => CompraPolicy::class,
        Cotizacion::class => CotizacionPolicy::class,
        Pedido::class => PedidoPolicy::class,
        Pago::class => PagoPolicy::class,
        Configuracion::class => ConfiguracionPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
