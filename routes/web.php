<?php

use App\Http\Controllers\CajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DepositoController;
use App\Http\Controllers\DepositoListaController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ExpedicionController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\OpcionesController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteProductoRmaController;
use App\Http\Controllers\ReporteProductoVendidoController;
use App\Http\Controllers\ReporteVentaController;
use App\Http\Controllers\RmaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RotacionStockController;
use App\Http\Controllers\TipoCambioController;
use App\Http\Controllers\TipoCambioYuanController;
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
/*Route::get('/demo', function () {
    return Inertia::render('Pixijs');
});*/


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
    Route::get('/productos/export', 'exportExcel')->name('productos.exportar')->middleware('auth');
    Route::get('/productos/{id}/exportproductoventas', 'exportProductoVentas')->name('productos.exportproductoventas')->middleware('auth');
    Route::get('/productos/actualizarfuturo', 'actualizarFuturo')->name('productos.actualizarfuturo')->middleware('auth');
    Route::get('/productos/actualizarYuanes', 'actualizarYuanes')->name('productos.actualizarYuanes')->middleware('auth');
    //Route::post('/productos/importar', 'importExcel')->name('productos.importar')->middleware('auth');
    Route::get('/productos/vistaimportar', 'vistaImportar')->name('productos.vistaimportar')->middleware('auth');
    Route::get('/productos/create', 'create')->name('productos.create')->middleware('auth');
    Route::get('/productos/{id}', 'edit')->name('productos.edit')->middleware('auth');
    Route::get('/productos/{id}/show', 'show')->name('productos.show')->middleware('auth');
    Route::post('/productos/update/{id}', 'update')->name('productos.update')->middleware('auth');
    Route::get('/productos', 'index')->name('productos.index')->middleware('auth');
    Route::post('/productos/store', 'store')->name('productos.store')->middleware('auth');
    Route::get('/productoventa/{id}/{inicio}/{fin}', 'productoVenta')->name('productos.productoventa')->middleware('auth');
    Route::get('/productoimportacion/{id}/{inicio}/{fin}', 'productoImportacion')->name('productos.productoimportacion')->middleware('auth');
    Route::delete('/productos/{id}', 'destroy')->name('productos.destroy')->middleware('auth');
});

//Importacion
Route::controller(ImportacionController::class)->group(function () {
    Route::delete('/importaciones/{id}', 'destroy')->name('importaciones.destroy')->middleware('auth');
    Route::get('/importaciones/create', 'create')->name('importaciones.create')->middleware('auth');
    Route::get('/importaciones/{id}', 'edit')->name('importaciones.edit')->middleware('auth');
    Route::get('/importaciones/{id}/export', 'exportExcel')->name('importaciones.exportar')->middleware('auth');
    Route::get('/importaciones/{id}/show', 'show')->name('importaciones.show')->middleware('auth');
    Route::get('/importaciones/{id}/showmodal', 'showModal')->name('importaciones.showmodal')->middleware('auth');
    Route::get('/importaciones/{id}/showproductomodal', 'showProductoModal')->name('importaciones.showproductomodal')->middleware('auth');
    Route::post('/importaciones/{id}/updateproducto', 'updateProducto')->name('importaciones.updateproducto')->middleware('auth');
    Route::post('/importaciones/update/{id}', 'update')->name('importaciones.update')->middleware('auth');
    Route::get('/importaciones', 'index')->name('importaciones.index')->middleware('auth');
    Route::post('/importaciones/store', 'store')->name('importaciones.store')->middleware('auth');
});

//TipoCambio
Route::controller(TipoCambioController::class)->group(function () {
    Route::post('/tipo-cambio/update/{id}', 'update')->name('tipo_cambio.update')->middleware('auth');
    Route::get('/tipo-cambio', 'index')->name('tipo_cambio.index')->middleware('auth');
    Route::get('/tipo-cambio/{id}', 'show')->name('tipo_cambio.show')->middleware('auth');
    Route::post('/tipo-cambio/store', 'store')->name('tipo_cambio.store')->middleware('auth');
    Route::delete('/tipo-cambio/{id}', 'destroy')->name('tipo_cambio.destroy')->middleware('auth');
});

//TipoCambioYuan
Route::controller(TipoCambioYuanController::class)->group(function () {
    Route::post('/tipo-cambio-yuan/update/{id}', 'update')->name('tipo_cambio_yuan.update')->middleware('auth');
    Route::get('/tipo-cambio-yuan', 'index')->name('tipo_cambio_yuan.index')->middleware('auth');
    Route::get('/tipo-cambio-yuan/{id}', 'show')->name('tipo_cambio_yuan.show')->middleware('auth');
    Route::post('/tipo-cambio-yuan/store', 'store')->name('tipo_cambio_yuan.store')->middleware('auth');
    Route::delete('/tipo-cambio-yuan/{id}', 'destroy')->name('tipo_cambio_yuan.destroy')->middleware('auth');
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
    Route::post('/envios/uploadexcel', 'uploadExcel')->name('envios.uploadexcel')->middleware('auth');
    Route::get('/envios/create', 'create')->name('envios.create')->middleware('auth');
    Route::get('/envios/historial', 'historialEnvios')->name('envios.historial')->middleware('auth');
    Route::post('/envios/store', 'store')->name('envios.store')->middleware('auth');
    Route::post('/envios/update/{id}', 'validarProductos')->name('envios.update')->middleware('auth');
    Route::get('/envios/detalle/{id}', 'detalle')->name('envios.detalle')->middleware('auth');
    Route::get('/envios', 'index')->name('envios.index')->middleware('auth');
    Route::get('/envios/ticket/{id}', 'generarTicket')->name('envios.generar_ticket')->middleware('auth');
    Route::get('/envios/{id}', 'show')->name('envios.show')->middleware('auth');

});

//DepositoLista
Route::controller(DepositoListaController::class)->group(function () {
    Route::post('/depositoslista/update/{id}', 'update')->name('depositoslista.update')->middleware('auth');
    Route::get('/depositoslista/{id}', 'show')->name('depositoslista.show')->middleware('auth');
    Route::get('/depositoslista', 'index')->name('depositoslista.index')->middleware('auth');
    Route::post('/depositoslista/store', 'store')->name('depositoslista.store')->middleware('auth');
});
//Deposito
Route::controller(DepositoController::class)->group(function () {
    Route::post('/depositos/update/{id}', 'update')->name('depositos.update')->middleware('auth');
    Route::get('/depositos/create', 'create')->name('depositos.create')->middleware('auth');
    Route::get('/depositos/bultos', 'bultos')->name('depositos.bultos')->middleware('auth');
    Route::get('/depositos/{id}/showproductomodal', 'showProductoModal')->name('depositos.showproductomodal')->middleware('auth');
    Route::get('/depositos/{id}/showcambiarproducto', 'showCambiarProducto')->name('depositos.showcambiarproducto')->middleware('auth');
    Route::get('/depositos/{id}/showmodal', 'showModal')->name('depositos.showmodal')->middleware('auth');
    Route::post('/depositos/update-deposito/{id}', 'updateDeposito')->name('depositos.updatedeposito')->middleware('auth');
    Route::post('/depositos/{id}/updateproducto', 'updateProducto')->name('depositos.updateproducto')->middleware('auth');
    Route::get('/depositos/historial', 'historial')->name('depositos.historial')->middleware('auth');
    Route::get('/depositos', 'index')->name('depositos.index')->middleware('auth');
    Route::get('/depositos/{id}', 'show')->name('depositos.show')->middleware('auth');
    Route::get('/depositos/{id}/export', 'exportExcel')->name('depositos.exportar')->middleware('auth');
    Route::post('/depositos/store', 'store')->name('depositos.store')->middleware('auth');
    Route::post('/depositos/showproductos', 'showProductos')->name('depositos.showproductos')->middleware('auth');
    Route::post('/depositos/destroyproductos', 'destroyProductos')->name('depositos.destroyproductos')->middleware('auth');
    Route::delete('/depositos/{id}', 'destroy')->name('depositos.destroy')->middleware('auth');
    Route::delete('/depositos/{id}/deposito', 'destroyDeposito')->name('depositos.destroydeposito')->middleware('auth');
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

//Rotacion
Route::controller(RotacionStockController::class)->group(function () {
    Route::get('/rotacion-stock/exportproductoventas', 'exportProductoVentas')->name('rotacion-stock.exportproductoventas')->middleware('auth');
    Route::get('/rotacion-stock', 'index')->name('rotacion-stock.index')->middleware('auth');
});

//Reporte Ventas
Route::get('/reportes-ventas', [ReporteVentaController::class, 'index'])->name('reportes.ventas')->middleware(['auth', 'verified']);

//Reporte Listado  productos
Route::get('/reportes-productos-vendidos', [ReporteProductoVendidoController::class, 'index'])->name('reportes.productosvendidos')->middleware(['auth', 'verified']);
Route::get('/reportes-productos-vendidos/exportproductoventas',[ReporteProductoVendidoController::class, 'exportProductoVentas'])->name('reportes.exportproductoventas')->middleware('auth');


//Rma -Presupuesto
Route::controller(RmaController::class)->group(function () {
    /*
    Route::post('/ventas/updatemercado/{id}', 'updatemercado')->name('ventas.updatemercado')->middleware('auth');*/
    Route::get('/rmas/subir/{id}', 'showsubir')->name('rmas.showsubir')->middleware('auth');
    Route::get('/rmas/subir', 'rma_subir')->name('rmas.subir')->middleware('auth');
    Route::post('/rmas/maestro', 'verificarCodigoMaestro')->name('rmas.maestro')->middleware('auth');
    Route::post('/rmas/rma-update/{id}', 'rma_update')->name('rmas.rma-update')->middleware('auth');
    Route::get('/rmas/rma-edit/{id}', 'rma_edit')->name('rmas.rma-edit')->middleware('auth');
    Route::post('/rmas/rma-store', 'rma_store')->name('rmas.rma-store')->middleware('auth');
    Route::post('/rmas/rma-subir', 'subir_store')->name('rmas.subir-store')->middleware('auth');
    Route::get('/rmas/rma-create', 'rma_create')->name('rmas.rma-create')->middleware('auth');
    Route::get('/rmas/stock-rma', 'rma_stock')->name('rmas.rma-stock')->middleware('auth');
    Route::get('/rmas/validacion', 'validacionRma')->name('rmas.validacion')->middleware('auth');
    Route::get('/rmas', 'index')->name('rmas.index')->middleware('auth');
    Route::get('/rmas/historial', 'historial')->name('rmas.historial')->middleware('auth');
    Route::get('/rmas/historial-envios', 'historialEnvios')->name('rmas.historial-envios')->middleware('auth');
    Route::get('/rmas/{id}/historial', 'showHistorial')->name('rmas.show-historial')->middleware('auth');
    Route::get('/rmas/{id}/validacion', 'validacionRmaShow')->name('rmas.show-validacion')->middleware('auth');
    Route::get('/rmas/{id}', 'show')->name('rmas.show')->middleware('auth');
    Route::get('/rmas/{id}/ticket', 'generarTicket')->name('rmas.generar_ticket')->middleware('auth');
    Route::delete('/rmas/{id}', 'destroy')->name('rmas.destroy')->middleware('auth');
    Route::delete('/rmas/{id}/stock', 'destroyStock')->name('rmas.destroy-stock')->middleware('auth');

});

//Reporte Listado  productos Rma
Route::get('/reportes-productos-rma', [ReporteProductoRmaController::class, 'index'])->name('reportes.productosrma')->middleware(['auth', 'verified']);
Route::get('/reportes-productos-rma/exportproductoventas',[ReporteProductoRmaController::class, 'exportProductoRma'])->name('reportes.exportproductorma')->middleware('auth');

require __DIR__.'/auth.php';
