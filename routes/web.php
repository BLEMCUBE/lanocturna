<?php

use App\Http\Controllers\OpcionesController;
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
require __DIR__.'/auth.php';
