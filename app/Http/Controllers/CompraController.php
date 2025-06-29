<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompraStoreRequest;
use App\Http\Requests\CompraUpdateRequest;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\CompraCollection;
use Exception;
use App\Models\Producto;
use App\Models\Compra;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CompraResource;
use App\Models\CostoReal;
use App\Models\TipoCambio;
use Illuminate\Support\Facades\Request;


class CompraController extends Controller
{
	public function __construct()
	{
		//protegiendo el controlador segun el rol
		//$this->middleware(['auth', 'permission:lista-compras'])->only('index');
		//$this->middleware(['auth', 'permission:crear-compras'])->only(['store','create']);
		//$this->middleware(['auth', 'permission:editar-compras'])->only(['update']);
		//$this->middleware(['auth', 'permission:eliminar-compras'])->only(['destroy']);
	}

	public function index()
	{


		$ultimo_tipo_cambio = TipoCambio::all()->last();

		$hoy_tipo_cambio = false;

		$actual = Carbon::now()->format('Y-m-d');
		if (!empty($ultimo_tipo_cambio)) {
			$fecha = Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
			if ($fecha->eq($actual)) {
				$hoy_tipo_cambio = true;
			} else {
				$hoy_tipo_cambio = false;
			}
		}

		$venta_query = Compra::select('*')->when(Request::input('inicio'), function ($query, $search) {
			$query->whereDate('created_at', '>=', $search);
		})
			->when(Request::input('fin'), function ($query, $search) {
				$query->whereDate('created_at', '<=', $search);
			})->orderBy('created_at', 'DESC')
			->get();
		return Inertia::render('Compra/Index', [
			'tipo_cambio' => $hoy_tipo_cambio,
			'ventas' => new CompraCollection(
				$venta_query
			)
		]);
	}
	public function create()
	{

		$ultimo_tipo_cambio = TipoCambio::all()->last();

		$hoy_tipo_cambio = false;
		$tipo_cambio = 0;
		$actual = Carbon::now()->format('Y-m-d');
		if (!empty($ultimo_tipo_cambio)) {
			$fecha = Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
			if ($fecha->eq($actual)) {
				$hoy_tipo_cambio = true;
				$tipo_cambio = number_format($ultimo_tipo_cambio->valor, 2) ?? '';
			} else {
				$hoy_tipo_cambio = false;
			}
		}
		$productoLista = Producto::with(['importacion_detalles' => function ($query) {
			$query->select('id', 'sku', 'cantidad_total', 'importacion_id', 'estado');
		}, 'importacion_detalles.importacion' => function ($query1) {
			$query1->select('id', 'estado', 'nro_carpeta');
		}])->select('id', 'nombre', 'origen', 'stock', 'codigo_barra', 'imagen')
			->orderBy('stock', 'ASC')
			->get();

		$resultadoProductoLista = new ProductoVentaCollection($productoLista);
		return Inertia::render('Compra/Create', [
			'hoy_tipo_cambio' => $hoy_tipo_cambio,
			'tipo_cambio' => $tipo_cambio,
			'productos' => $resultadoProductoLista
		]);
	}
	public function edit($id)
	{
		$hoy = Carbon::now()->format('Y-m-d');
		$compra = Compra::with(['detalles_compras' => function ($query)use($hoy) {
			$query->select('*')->with(['producto' => function ($query){
				$query->select('id', 'nombre', 'codigo_barra', 'origen');
			},'costo_reales'=>function($query)use($hoy){
				$query
				->orderBy('fecha', 'DESC')
				//->whereNot('monto', '=', 0)
			->whereDate('fecha','<=',$hoy)
			->select('origen','monto','producto_id','id','fecha')
			->limit(1)->first();
			}])
			;
		}])
			->with(['facturador' => function ($query) {
				$query->select('id', 'name');
			}])->select('*')
			->orderBy('id', 'DESC')->findOrFail($id);
		$productoLista = Producto::with(['importacion_detalles' => function ($query) {
			$query->select('id', 'sku', 'cantidad_total', 'importacion_id', 'estado');
		}, 'importacion_detalles.importacion' => function ($query1) {
			$query1->select('id', 'estado', 'nro_carpeta');
		}])->select('id', 'nombre', 'origen', 'stock', 'codigo_barra', 'imagen')
			->orderBy('stock', 'ASC')
			->get();
			//return response()->json($compra);

		$resultadoProductoLista = new ProductoVentaCollection($productoLista);
		return Inertia::render('Compra/Edit', [
			'compra' => $compra,
			'productos' => $resultadoProductoLista
		]);
	}

	public function store(CompraStoreRequest $request)
	{

		$usuario = auth()->user();
		$hoy = Carbon::now()->format('Y-m-d');
		DB::beginTransaction();
		try {

			//creando compra
			$compra = Compra::create([
				'nro_factura' => $request->nro_factura ?? '',
				'proveedor' => $request->proveedor,
				'observaciones' => $request->observaciones,
				'facturador_id' => $usuario->id,
				'total_sin_iva' => $request->total_sin_iva ?? 0,
				'total' => $request->total ?? 0,
				//'estado' => $request->estado,
				'moneda' => $request->moneda,
				'tipo_cambio' => $request->tipo_cambio,
				'comprador_id' => $usuario->id,

			]);

			//creando detalle compra
			foreach ($request->productos as $producto) {

				$det = $compra->detalles_compras()->create(
					[
						"producto_id" => $producto['producto_id'],
						"precio" => $producto['precio'],
						"precio_sin_iva" => $producto['precio_sin_iva'],
						"cantidad" => $producto['cantidad'],
						"total" => $producto['total'],
						"total_sin_iva" => $producto['total_sin_iva'],

					]
				);

				/*$costo_real_reg = CostoReal::select('*')
					->where('producto_id', '=', $producto['producto_id'])
					->where('compra_id', '=', $compra->id)
					->where('origen', '=','COMPRA')
					->where('compra_detalle_id', '=', $det->id)
					->whereDate('fecha', '=', $hoy)->first();

				if (!is_null($costo_real_reg)) {
					$costo_real_reg->update([
						"monto" => $producto['costo_real'],
						"origen" => 'COMPRA',
						"creador_id" => $usuario->id,

					]);
				} else {*/
					$produc = Producto::select('id', 'origen')->where('id', '=', $producto['producto_id'])
						->first();
					CostoReal::create([
						"fecha" => $compra->created_at->format('Y-m-d'),
						"sku" => $produc->origen,
						"origen" => 'COMPRA',
						//"monto" => $producto['costo_real'],
						"monto" => $producto['precio'],
						"producto_id" => $producto['producto_id'],
						"compra_id" =>  $compra->id,
						"compra_detalle_id" =>$det->id,
						"creador_id" => $usuario->id,
					]);
				//}
			}

			//actualizando stock producto
			foreach ($compra->detalles_compras as $produc) {
				$prod = Producto::find($produc['producto_id']);
				$old_stock = $prod->stock;

				$new_stock = $old_stock + $produc['cantidad'];
				$prod->update([
					"stock" => $new_stock,
					"stock_futuro" => $new_stock + $prod->en_camino
				]);
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
	public function update(CompraUpdateRequest $request, $id)
	{
		$venta = Compra::findOrFail($id);
		$hoy = Carbon::now()->format('Y-m-d');
		$usuario = auth()->user();
		DB::beginTransaction();
		try {
			$venta->nro_factura = $request->nro_factura;
			$venta->proveedor = $request->proveedor;
			$venta->observaciones = $request->observaciones;
			$venta->total_sin_iva =  $request->total_sin_iva ?? 0;
			$venta->total =  $request->total ?? 0;
			$venta->moneda = $request->moneda;
			$venta->tipo_cambio = $request->tipo_cambio;
			$venta->comprador_id = $request->vendedor_id;

			$venta->save();

			//actualizando stock producto
			foreach ($venta->detalles_compras as $producto) {
				$prod = Producto::find($producto['producto_id']);
				$old_stock = $prod->stock;
				$new_stock = $old_stock - $producto['cantidad'];
				$prod->update([
					"stock" => $new_stock,
					"stock_futuro" => $new_stock + $prod->en_camino
				]);
			}
			//eliminando  detalle
			$venta->detalles_compras()->delete();

			//creando detalle compra
			foreach ($request->productos as $producto) {


				$det = $venta->detalles_compras()->create(
					[
						"producto_id" => $producto['producto_id'],
						"precio" => $producto['precio'],
						"precio_sin_iva" => $producto['precio_sin_iva'],
						"cantidad" => $producto['cantidad'],
						"total" => $producto['total'],
						"total_sin_iva" => $producto['total_sin_iva'],
					]
				);

					/*$costo_real_reg = CostoReal::select('*')
					->where('producto_id', '=', $producto['producto_id'])
					->where('compra_id', '=', $venta->id)
					->where('origen', '=','COMPRA')
					->where('compra_detalle_id', '=', $det->id)
					->whereDate('fecha', '=', $hoy)->first();*/

					$costo_real_reg = CostoReal::select('*')
							->where('producto_id', '=', $request->input('id'))
					->where('id', '=', $request->input('costo_id'))
					->first();

					if (!is_null($costo_real_reg)) {
					$costo_real_reg->update([
						"monto" => $producto['precio'],
						//"monto" => $producto['costo_real'],
						"origen" => $producto['costo_fecha'],
						"creador_id" => $usuario->id,
						"compra_id" =>  $venta->id,
						"compra_detalle_id" =>$det->id,

					]);
				} else {
					$produc = Producto::select('id', 'origen')->where('id', '=', $producto['producto_id'])
						->first();
					CostoReal::create([
						"fecha" => $venta->created_at->format('Y-m-d'),
						"sku" => $produc->origen,
						"origen" => 'COMPRA',
						//"monto" => $producto['costo_real'],
						"monto" => $producto['precio'],
						"producto_id" => $producto['producto_id'],
						"compra_id" =>  $venta->id,
						"compra_detalle_id" =>$det->id,
						"creador_id" => $usuario->id,
					]);
				}
			}

			//actualizando stock producto
			foreach ($request->productos  as $proo2) {
				$prod = Producto::find($proo2['producto_id']);
				$old_stock = $prod->stock;
				$new_stock = $old_stock + $proo2['cantidad'];
				$prod->update([
					"stock" => $new_stock,
					"stock_futuro" => $new_stock + $prod->en_camino
				]);
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
		$venta_query = Compra::with(['detalles_compras' => function ($query) {
			$query->select('compra_detalles.*')->with(['producto' => function ($query) {
				$query->select('id', 'nombre', 'codigo_barra', 'origen');
			}]);
		}])
			->with(['facturador' => function ($query) {
				$query->select('id', 'name');
			}])->select('*')->orderBy('id', 'DESC')->findOrFail($id);
		$compra = new CompraResource($venta_query);

		return Inertia::render('Compra/Show', [
			'compra' => $compra
		]);
	}

	public function destroy($id)
	{
		$venta = Compra::find($id);

		DB::beginTransaction();
		try {
			$venta->estado = "ANULADO";
			$venta->fecha_anulacion =  now();
			$venta->save();

			//actualizando stock producto
			foreach ($venta->detalles_compras as $producto) {
				$prod = Producto::find($producto['producto_id']);
				$old_stock = $prod->stock;
				$new_stock = $old_stock - $producto['cantidad'];
				$prod->update([
					"stock" => $new_stock,
					"stock_futuro" => $new_stock + $prod->en_camino
				]);
			}

			//eliminando  detalle
			$venta->detalles_compras()->delete();

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
