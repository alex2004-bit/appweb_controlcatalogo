<?php

use App\Http\Controllers\Admin\Catalogo\CategoriaController;
use App\Http\Controllers\Admin\Catalogo\GaleriaController;
use App\Http\Controllers\Admin\Catalogo\MarcaController;
use App\Http\Controllers\Admin\Catalogo\ProductoController;
use App\Http\Controllers\Admin\Catalogo\ServicioController;
use App\Http\Controllers\Admin\Clientes\ClienteController;
use App\Http\Controllers\Admin\Clientes\ConsultaController;
use App\Http\Controllers\Admin\Configuracion\EmpresaController;
use App\Http\Controllers\Admin\Configuracion\GeneralesController;
use App\Http\Controllers\Admin\Configuracion\SocialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Finanzas\PagoController;
use App\Http\Controllers\Admin\Finanzas\ReporteController;
use App\Http\Controllers\Admin\Pedidos\PedidoController;
use App\Http\Controllers\Admin\Seguridad\PermissionController;
use App\Http\Controllers\Admin\Seguridad\RoleController;
use App\Http\Controllers\Admin\Seguridad\UserController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('productos', [PublicController::class, 'catalog'])->name('productos');
Route::get('productos/{producto:slug}', [PublicController::class, 'catalogShow'])->name('productos.show');
Route::get('novedades', [PublicController::class, 'newProducts'])->name('novedades');
Route::get('ofertas', [PublicController::class, 'offers'])->name('ofertas');
Route::get('servicios', [PublicController::class, 'services'])->name('servicios');
Route::get('nosotros', [PublicController::class, 'nosotros'])->name('nosotros');
Route::get('contacto', [PublicController::class, 'contacto'])->name('contacto');
Route::post('contacto', [PublicController::class, 'sendContact'])->name('contacto.send');
Route::get('menu-data', [PublicController::class, 'menuData'])->name('menu.data');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::prefix('seguridad')->name('seguridad.')->group(function () {
                Route::resource('users', UserController::class)->except(['show']);
                Route::resource('roles', RoleController::class)->except(['show']);
                Route::get('permisos', [PermissionController::class, 'index'])->name('permisos.index');
                Route::put('permisos', [PermissionController::class, 'update'])->name('permisos.update');
            });

            Route::prefix('catalogo')->name('catalogo.')->group(function () {
                Route::resource('categorias', CategoriaController::class)->except(['show']);
                Route::resource('marcas', MarcaController::class)->except(['show']);
                Route::resource('productos', ProductoController::class)->except(['show']);
                Route::resource('servicios', ServicioController::class)->except(['show']);
                Route::get('galeria', [GaleriaController::class, 'index'])->name('galeria.index');
                Route::post('galeria', [GaleriaController::class, 'store'])->name('galeria.store');
                Route::put('galeria/{galeria}', [GaleriaController::class, 'update'])->name('galeria.update');
                Route::delete('galeria/{galeria}', [GaleriaController::class, 'destroy'])->name('galeria.destroy');
            });

            Route::prefix('clientes')->name('clientes.')->group(function () {
                Route::get('/', [ClienteController::class, 'index'])->name('index');
                Route::get('create', [ClienteController::class, 'create'])->name('create');
                Route::post('/', [ClienteController::class, 'store'])->name('store');
                Route::get('{cliente}/edit', [ClienteController::class, 'edit'])->name('edit');
                Route::put('{cliente}', [ClienteController::class, 'update'])->name('update');
                Route::delete('{cliente}', [ClienteController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('consultas')->name('consultas.')->group(function () {
                Route::get('/', [ConsultaController::class, 'index'])->name('index');
                Route::get('{consulta}', [ConsultaController::class, 'show'])->name('show');
                Route::put('{consulta}', [ConsultaController::class, 'update'])->name('update');
                Route::delete('{consulta}', [ConsultaController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('cotizaciones')->name('cotizaciones.')->group(function () {
                Route::get('/', [CotizacionController::class, 'index'])->name('index');
                Route::get('create', [CotizacionController::class, 'create'])->name('create');
                Route::post('/', [CotizacionController::class, 'store'])->name('store');
                Route::get('{cotizacion}', [CotizacionController::class, 'show'])->name('show');
                Route::get('{cotizacion}/edit', [CotizacionController::class, 'edit'])->name('edit');
                Route::put('{cotizacion}', [CotizacionController::class, 'update'])->name('update');
                Route::delete('{cotizacion}', [CotizacionController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('pedidos')->name('pedidos.')->group(function () {
                Route::get('pedidos', [PedidoController::class, 'index'])->name('index');
                Route::get('pedidos/create', [PedidoController::class, 'create'])->name('create');
                Route::post('pedidos', [PedidoController::class, 'store'])->name('store');
                Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->name('show');
                Route::get('pedidos/{pedido}/edit', [PedidoController::class, 'edit'])->name('edit');
                Route::put('pedidos/{pedido}', [PedidoController::class, 'update'])->name('update');
                Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('finanzas')->name('finanzas.')->group(function () {
                Route::get('pagos', [PagoController::class, 'index'])->name('pagos.index');
                Route::get('pagos/create', [PagoController::class, 'create'])->name('pagos.create');
                Route::post('pagos', [PagoController::class, 'store'])->name('pagos.store');
                Route::get('pagos/{pago}', [PagoController::class, 'show'])->name('pagos.show');
                Route::get('pagos/{pago}/edit', [PagoController::class, 'edit'])->name('pagos.edit');
                Route::put('pagos/{pago}', [PagoController::class, 'update'])->name('pagos.update');
                Route::delete('pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
                Route::get('pagos/{pago}/pdf', [PagoController::class, 'pdf'])->name('pagos.pdf');
                Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
            });

            Route::prefix('configuracion')->name('configuracion.')->group(function () {
                Route::get('empresa', [EmpresaController::class, 'index'])->name('empresa.index');
                Route::put('empresa', [EmpresaController::class, 'update'])->name('empresa.update');
                Route::get('social', [SocialController::class, 'index'])->name('social.index');
                Route::put('social', [SocialController::class, 'update'])->name('social.update');
                Route::get('generales', [GeneralesController::class, 'index'])->name('generales.index');
                Route::put('generales', [GeneralesController::class, 'update'])->name('generales.update');
            });
        });
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
