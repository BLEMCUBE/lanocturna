<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
	return $request->user();
});

//Venta
Route::controller(VentaWebController::class)->prefix('ventasweb')->name('ventasweb.')->middleware('guest')->group(function () {
	Route::post('/store', 'store')->name('store');
});

//Producto
Route::controller(ProductoController::class)->prefix('productos')->name('productos.')->middleware('guest')->group(function () {
	Route::post('/updated-price-multiple', 'updatedPriceMultiple')->name('updated_price_multiple');
	Route::post('/{sku}/updated-price', 'updatedPrice')->name('updated_price');
});
