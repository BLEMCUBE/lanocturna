<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\OpcionesController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioController;
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


Route::get('/inicio', function () {
    return Inertia::render('Inicio');
})->middleware(['auth', 'verified'])->name('inicio');

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
//Producto

Route::controller(ImportacionController::class)->group(function () {
    Route::get('/importaciones/create', 'create')->name('importaciones.create')->middleware('auth');
    Route::get('/importaciones/{id}', 'edit')->name('importaciones.edit')->middleware('auth');
    Route::get('/importaciones/{id}/show', 'show')->name('importaciones.show')->middleware('auth');
    Route::post('/importaciones/update/{id}', 'update')->name('importaciones.update')->middleware('auth');
    Route::get('/importaciones', 'index')->name('importaciones.index')->middleware('auth');
    Route::post('/importaciones/store', 'store')->name('importaciones.store')->middleware('auth');
    Route::delete('/importaciones/{id}', 'destroy')->name('importaciones.destroy')->middleware('auth');
});


require __DIR__.'/auth.php';
