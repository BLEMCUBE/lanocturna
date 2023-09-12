<?php

use App\Http\Controllers\CajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DepositoController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ExpedicionController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\OpcionesController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoCambioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return Inertia::render('Auth/Login');
});


Route::get('/inicio', [InicioController::class, 'index'])->name('inicio')->middleware(['auth', 'verified']);

//Roles
Route::controller(RoleController::class)->group(function () {
    Route::get('/roles/{id}', 'edit')->name('roles.edit')->middleware('auth');
    Route::post('/roles/{id}', 'update')->name('roles.update')->middleware('auth');
    Route::get('/roles', 'index')->name('roles.index')->middleware('auth');

});

//opciones
Route::controller(OpcionesController::class)->group(function () {
    Route::get('/opciones/roles', 'getRoles')->name('opciones.roles')->middleware('auth');

});
//cofiguraciones
Route::controller(ConfiguracionController::class)->group(function () {
    Route::get('/configuraciones', 'index')->name('configuraciones.index')->middleware('auth');
    Route::post('/configuraciones/update/{id}', 'update')->name('configuraciones.update')->middleware('auth');


});

//Usuario
Route::controller(UsuarioController::class)->group(function () {
    Route::post('/usuarios/update/{id}', 'update')->name('usuarios.update')->middleware('auth');
    Route::get('/usuarios', 'index')->name('usuarios.index')->middleware('auth');
    Route::get('/usuarios/{id}', 'show')->name('usuarios.show')->middleware('auth');
    Route::post('/usuarios/store', 'store')->name('usuarios.store')->middleware('auth');
    Route::delete('/usuarios/{id}', 'destroy')->name('usuarios.destroy')->middleware('auth');
});

//Cliente
Route::controller(ClienteController::class)->group(function () {
    Route::post('/clientes/update/{id}', 'update')->name('clientes.update')->middleware('auth');
    Route::get('/clientes', 'index')->name('clientes.index')->middleware('auth');
    Route::get('/clientes/{id}', 'show')->name('clientes.show')->middleware('auth');
    Route::post('/clientes/store', 'store')->name('clientes.store')->middleware('auth');
    Route::delete('/clientes/{id}', 'destroy')->name('clientes.destroy')->middleware('auth');
});

//Producto
Route::controller(ProductoController::class)->group(function () {
    Route::get('/productos/create', 'create')->name('productos.create')->middleware('auth');
    Route::get('/productos/{id}', 'edit')->name('productos.edit')->middleware('auth');
    Route::get('/productos/{id}/show', 'show')->name('productos.show')->middleware('auth');
    Route::post('/productos/update/{id}', 'update')->name('productos.update')->middleware('auth');
    Route::get('/productos', 'index')->name('productos.index')->middleware('auth');
    Route::post('/productos/store', 'store')->name('productos.store')->middleware('auth');
    Route::delete('/productos/{id}', 'destroy')->name('productos.destroy')->middleware('auth');
});

//Importacion
Route::controller(ImportacionController::class)->group(function () {
    Route::get('/importaciones/create', 'create')->name('importaciones.create')->middleware('auth');
    Route::get('/importaciones/{id}', 'edit')->name('importaciones.edit')->middleware('auth');
    Route::get('/importaciones/{id}/show', 'show')->name('importaciones.show')->middleware('auth');
    Route::post('/importaciones/update/{id}', 'update')->name('importaciones.update')->middleware('auth');
    Route::get('/importaciones', 'index')->name('importaciones.index')->middleware('auth');
    Route::post('/importaciones/store', 'store')->name('importaciones.store')->middleware('auth');
    Route::delete('/importaciones/{id}', 'destroy')->name('importaciones.destroy')->middleware('auth');
    Route::get('/importaciones/{id}/export', 'exportExcel')->name('importaciones.exportar')->middleware('auth');
});

//TipoCambio
Route::controller(TipoCambioController::class)->group(function () {
    Route::post('/tipo-cambio/update/{id}', 'update')->name('tipo_cambio.update')->middleware('auth');
    Route::get('/tipo-cambio', 'index')->name('tipo_cambio.index')->middleware('auth');
    Route::get('/tipo-cambio/{id}', 'show')->name('tipo_cambio.show')->middleware('auth');
    Route::post('/tipo-cambio/store', 'store')->name('tipo_cambio.store')->middleware('auth');
    Route::delete('/tipo-cambio/{id}', 'destroy')->name('tipo_cambio.destroy')->middleware('auth');
});

//Venta
Route::controller(VentaController::class)->group(function () {
    Route::post('/ventas/update/{id}', 'update')->name('ventas.update')->middleware('auth');
    Route::post('/ventas/updatemercado/{id}', 'updatemercado')->name('ventas.updatemercado')->middleware('auth');
    Route::get('/ventas/edit/{id}', 'edit')->name('ventas.edit')->middleware('auth');
    Route::get('/ventas/create', 'create')->name('ventas.create')->middleware('auth');
    Route::post('/ventas/store', 'store')->name('ventas.store')->middleware('auth');
    Route::get('/ventas', 'index')->name('ventas.index')->middleware('auth');
    Route::get('/ventas/{id}', 'show')->name('ventas.show')->middleware('auth');
    Route::delete('/ventas/{id}', 'destroy')->name('ventas.destroy')->middleware('auth');

});

//Caja
Route::controller(CajaController::class)->group(function () {
    Route::post('/cajas/update/{id}', 'update')->name('cajas.update')->middleware('auth');
    Route::get('/cajas/edit/{id}', 'edit')->name('cajas.edit')->middleware('auth');
    Route::get('/cajas/facturar/{id}', 'facturar')->name('cajas.facturar')->middleware('auth');
    Route::get('/cajas', 'index')->name('cajas.index')->middleware('auth');
    Route::get('/cajas/{id}', 'show')->name('cajas.show')->middleware('auth');
});

//Expedicion
Route::controller(ExpedicionController::class)->group(function () {
    Route::post('/expediciones/maestro', 'verificarCodigoMaestro')->name('expediciones.maestro')->middleware('auth');
    Route::post('/expediciones/update/{id}', 'validarProductos')->name('expediciones.update')->middleware('auth');
    Route::get('/expediciones', 'index')->name('expediciones.index')->middleware('auth');
    Route::get('/expediciones/{id}', 'show')->name('expediciones.show')->middleware('auth');
});

//Envio
Route::controller(EnvioController::class)->group(function () {
    Route::post('/envios/maestro', 'verificarCodigoMaestro')->name('envios.maestro')->middleware('auth');
    Route::get('/envios/create', 'create')->name('envios.create')->middleware('auth');
    Route::get('/envios/historial', 'historialEnvios')->name('envios.historial')->middleware('auth');
    Route::post('/envios/store', 'store')->name('envios.store')->middleware('auth');
    Route::post('/envios/update/{id}', 'validarProductos')->name('envios.update')->middleware('auth');
    Route::get('/envios/detalle/{id}', 'detalle')->name('envios.detalle')->middleware('auth');
    Route::get('/envios', 'index')->name('envios.index')->middleware('auth');
    Route::get('/envios/ticket/{id}', 'generarTicket')->name('envios.generar_ticket')->middleware('auth');
    Route::get('/envios/{id}', 'show')->name('envios.show')->middleware('auth');

});

//Deposito
Route::controller(DepositoController::class)->group(function () {
    Route::post('/depositos/update/{id}', 'update')->name('depositos.update')->middleware('auth');
    Route::post('/depositos/update-deposito/{id}', 'updateDeposito')->name('depositos.updateDeposito')->middleware('auth');
    Route::get('/depositos/nombres', 'nombres')->name('depositos.nombres')->middleware('auth');
    Route::get('/depositos', 'index')->name('depositos.index')->middleware('auth');
    Route::get('/depositos/{id}', 'show')->name('depositos.show')->middleware('auth');
    Route::post('/depositos/store', 'store')->name('depositos.store')->middleware('auth');
    Route::delete('/depositos/{id}', 'destroy')->name('depositos.destroy')->middleware('auth');
});

//Compra
Route::controller(CompraController::class)->group(function () {
    Route::post('/compras/update/{id}', 'update')->name('compras.update')->middleware('auth');
    Route::get('/compras/edit/{id}', 'edit')->name('compras.edit')->middleware('auth');
    Route::get('/compras/create', 'create')->name('compras.create')->middleware('auth');
    Route::post('/compras/store', 'store')->name('compras.store')->middleware('auth');
    Route::get('/compras', 'index')->name('compras.index')->middleware('auth');
    Route::get('/compras/{id}', 'show')->name('compras.show')->middleware('auth');
    Route::delete('/compras/{id}', 'destroy')->name('compras.destroy')->middleware('auth');

});


require __DIR__.'/auth.php';
