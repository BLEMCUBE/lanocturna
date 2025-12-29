<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentaUpdateRequest;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\VentaCollection;
use App\Models\Cliente;
use Exception;
use App\Models\Producto;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VentaResource;
use App\Models\Destino;
use App\Jobs\WCActualizarStockJob;

class CajaController extends Controller
{
	public function __construct() {}

	public function index()
	{


		return Inertia::render('Caja/Index', [
			'ventas' => new VentaCollection(
				Venta::where('estado', "PENDIENTE DE FACTURACIÓN")->orderBy('created_at', 'DESC')
					->get()
			)
		]);
	}

	public function edit($id)
	{
		//Lista cliente
		$lista_clientes = Cliente::get();
		$lista_cliente = Cliente::select('id', 'nombre')->get();

		$clientes = [];
		foreach ($lista_clientes as $cliente) {
			array_push($clientes, [
				'value' => $cliente->id,
				'label' => $cliente->nombre,
			]);
		}
		//Lista destino
		$lista_destin = Destino::get();

		$lista_destinos = [];
		foreach ($lista_destin as $destino) {
			array_push($lista_destinos, [
				'value' => $destino->nombre,
				'label' =>  $destino->nombre,
			]);
		}

		$venta = Venta::with(['detalles_ventas' => function ($query) {
			$query->select('venta_detalles.*')->with(['producto' => function ($query) {
				$query->select('id', 'nombre', 'imagen', 'codigo_barra', 'origen');
			}]);
		}])
			->with(['vendedor' => function ($query) {
				$query->select('users.id', 'users.name');
			}])
			->orderBy('id', 'DESC')->findOrFail($id);
		return Inertia::render('Caja/Edit', [
			'lista_destinos' => $lista_destinos,
			'venta' => $venta,
			'productos' => new ProductoVentaCollection(
				Producto::orderBy('created_at', 'DESC')
					->get()
			)
		]);
	}


	public function update(VentaUpdateRequest $request, $id)
	{
		$venta = Venta::find($id);

		DB::beginTransaction();
		try {
			$venta->codigo = $request->codigo;
			$venta->total_sin_iva =  $request->total_sin_iva ?? 0;
			$venta->total =  $request->total ?? 0;
			$venta->moneda = $request->moneda;
			$venta->tipo_cambio = $request->tipo_cambio;
			$venta->destino = $request->destino;
			$venta->cliente = json_encode($request->cliente);
			$venta->observaciones = $request->observaciones;
			$venta->vendedor_id = $request->vendedor_id;
			$venta->save();

			//eliminando  detalle
			$venta->detalles_ventas()->delete();

			//creando detalle venta
			foreach ($request->productos as $producto) {

				$venta->detalles_ventas()->create(
					[
						"producto_id" => $producto['producto_id'],
						"precio" => $producto['precio'],
						"precio_sin_iva" => $producto['precio_sin_iva'],
						"cantidad" => $producto['cantidad'],
						"total" => $producto['total'],
						"total_sin_iva" => $producto['total_sin_iva'],
					]
				);
			}


			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}
	public function show($id)
	{
		$venta_query = Venta::with(['detalles_ventas' => function ($query) {
			$query->select('venta_detalles.*')->with(['producto' => function ($query) {
				$query->select('id', 'nombre', 'imagen', 'codigo_barra', 'origen');
			}]);
		}])
			->with(['vendedor' => function ($query) {
				$query->select('users.id', 'users.name');
			}])
			->with(['facturador' => function ($query) {
				$query->select('id', 'name');
			}])
			->with(['validador' => function ($query) {
				$query->select('id', 'name');
			}])
			->orderBy('id', 'DESC')->findOrFail($id);
		//return $venta_query;

		$venta = new VentaResource($venta_query);
		return Inertia::render('Caja/Show', [
			'venta' => $venta
		]);
	}

	public function facturar($id)
	{

		$venta = Venta::with('detalles_ventas')->orderBy('id', 'DESC')->findOrFail($id);
		$facturador = auth()->user();

		DB::beginTransaction();
		try {
			$venta->estado = "FACTURADO";
			$venta->facturado = true;
			$venta->facturador_id =  $facturador->id;
			$venta->fecha_facturacion = now();
			$venta->save();

			//actualizando stock producto
			foreach ($venta->detalles_ventas as $producto) {
				$prod = Producto::find($producto['producto_id']);
				$old_stock = $prod->stock;
				$new_stock = $old_stock - $producto['cantidad'];
				$prod->update([
					"stock" => $new_stock,
					"stock_futuro" => $new_stock + $prod->en_camino
				]);
				//actualizar stock web
				dispatch((new WCActualizarStockJob($prod->origen, $new_stock))->onQueue('meli'));
			}

			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}
}
