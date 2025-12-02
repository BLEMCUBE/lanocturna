<?php

namespace App\Http\Controllers;

use App\Exports\DepositoExport;
use App\Http\Requests\CambiarDepositoRequest;
use App\Http\Requests\DepositoImportacionUpdateRequest;
use App\Http\Requests\DepositoStoreRequest;
use App\Http\Requests\DepositoUpdateRequest;
use App\Http\Resources\DepositoCollection;
use App\Http\Resources\DepositoHistorialCollection;
use App\Imports\DepositoImport;
use App\Models\Deposito;
use App\Models\DepositoDetalle;
use App\Models\DepositoHistorial;
use App\Models\DepositoLista;
use App\Models\DepositoProducto;
use App\Models\Producto;
use App\Services\ProductoService;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Req;

class DepositoController extends Controller
{
		public function __construct(
		private ProductoService $productoService
	) {}

	public function index()
	{

		$query_depositos = DepositoLista::with(['depositos_productos' => function ($query) {
			$query->select('id', 'sku', 'bultos', 'pcs_bulto', 'deposito_lista_id', 'cantidad_total')
				->where('bultos', '>', 0)
				->with(['producto' => function ($query) {
					$query->select('id', 'origen', 'nombre', 'imagen', 'codigo_barra');
				}]);
		}])->select('id', 'nombre')->orderBy('nombre', 'ASC')->get();


		$depositos = [];
		$det_producto = [];

		foreach ($query_depositos as $deposito) {

			foreach ($deposito->depositos_productos as $prod) {

				$pr = Producto::where('origen', $prod->sku)
					->select('id', 'nombre', 'imagen')
					->with(['costos_reales' => function ($query) {
						$query->whereNot('monto', '=', 0)
							->select('monto', 'producto_id')->orderBy('fecha', 'DESC')->limit(1)->first();
					}])
					->first();


				array_push($det_producto, [
					"id" => $prod->id,
					"sku" => $prod->sku,
					"bultos" => $prod->bultos,
					"pcs_bulto" => $prod->pcs_bulto,
					"cantidad_total" => $prod->cantidad_total,
					"costo_real" => count($pr->costos_reales) > 0 ? $pr->costos_reales[0]['monto'] : 0,
					"total" => count($pr->costos_reales) > 0 ? $prod->cantidad_total * $pr->costos_reales[0]['monto'] : 0,
					"producto_id" => $pr->id,
					"nombre" => $pr->nombre,
					"imagen" => $pr->imagen,
					'deposito_lista_id' => $deposito->id
				]);
			}
			array_push($depositos, [
				"id" => $deposito->id,
				"nombre" => $deposito->nombre,
				"productos" => $det_producto,

			]);
			$det_producto = [];
		}
		//return response()->json($depositos);
		//return $depositos;
		return Inertia::render('Deposito/Index', [
			'depositos' => $depositos
		]);
	}


	public function bultos()
	{
		return Inertia::render('Deposito/Bultos', [
			'productos' => new DepositoCollection(
				Deposito::orderBy('id', 'DESC')
					->get()
			)
		]);
	}

	public function create()
	{
		return Inertia::render('Deposito/Create');
	}

	public function historial()
	{

		return Inertia::render('Deposito/Historial', [
			'historial' => new DepositoHistorialCollection(
				DepositoHistorial::orderBy('id', 'DESC')
					->get()
			)
		]);
	}

	public function store(DepositoStoreRequest $request)
	{

		$usuario = auth()->user();
		$file = $request->file('archivo');
		$no_existe = [];
		$filas = Excel::toArray([], $file);

		foreach ($filas[0] as $key => $fila) {
			$prod = Producto::where('origen', '=', $fila[0])->first();
			if (empty($prod)) {
				array_push($no_existe, [
					'fila' => $key + 1,
					'sku' => $fila[0],
				]);
			}
		}
		if (count($no_existe) > 1) {

			throw ValidationException::withMessages([
				'filas' => [$no_existe]
			]);
		} else {

			DB::beginTransaction();
			try {

				//creando deposito
				$deposito = Deposito::create([
					'nro_carpeta' => $request->nro_carpeta ?? '',
					'nro_contenedor' => $request->nro_contenedor ?? '',
					'estado' => $request->estado ?? '',
					'total' => $request->total ?? 0,
					'fecha_arribado' => $request->fecha_arribado ?? '',
					'fecha_camino' => $request->fecha_camino ?? '',
					'user_id' => $usuario->id

				]);

				///importando excel
				Excel::import(new DepositoImport($deposito->id, $deposito->estado), $file);


				DB::commit();
				return Redirect::route('depositos.bultos')->with([
					// 'success' =>  $venta->codigo
				]);
			} catch (Exception $e) {
				DB::rollBack();
				return [
					'success' => false,
					'message' => $e->getMessage(),
				];
			}
		}
	}

	public function showModal($id)
	{

		$deposito = Deposito::select(DB::raw("*,DATE_FORMAT(created_at,'%d/%m/%y %H:%i:%s') AS fecha,
            FORMAT(total, 2, 'en_US') as total"))
			->where('id', $id)->first();
		$importacion = Deposito::find($id);

		$detalle_deposito = DepositoDetalle::with(['producto' => function ($query) {
			$query->select('id', 'nombre', 'codigo_barra', 'origen');
		}])->select('*')->where('deposito_id', $id)->get();

		$lista_productos_movidos = [];

		if ($importacion->estado == 'Arribado') {
			foreach ($detalle_deposito as $producto) {
				$datoProducto = DepositoProducto::where('sku', $producto->sku)->where('deposito_lista_id', 1)
					->where('pcs_bulto', '=', $producto->pcs_bulto)
					->first();
				if (!empty($datoProducto) || !is_null($datoProducto)) {

					if ($producto->bultos > $datoProducto->bultos) {
						$mover = ($producto->bultos >= $datoProducto->bultos) ? $producto->bultos - $datoProducto->bultos : $producto->bultos;
						array_push($lista_productos_movidos, [
							"sku" => $datoProducto->sku,
							"bultos_deposito" => $datoProducto->bultos,
							"bultos_importado" => $producto->bultos,
							"mover" => $mover,
							"producto" => $producto->producto->nombre,
						]);
					}
				}
			}
		}

		if (count($lista_productos_movidos) > 0) {
			return response()->json([
				"status" => false,
				"faltantes" => $lista_productos_movidos
			]);
		} else {
			return response()->json([
				"status" => true,
				"deposito" => $deposito
			]);
		}
	}
	public function showProductoModal($id)
	{

		$importacion_detalle = DepositoDetalle::with(['deposito' => function ($query) {
			$query->select('*');
		}])->with(['producto' => function ($query) {
			$query->select('id', 'nombre', 'codigo_barra', 'origen');
		}])->where('id', $id)->first();
		return response()->json([
			"importacion_detalle" => $importacion_detalle
		]);
	}
	public function showCambiarProducto($id)
	{

		$deposito_detalle = DepositoProducto::with(['deposito_lista' => function ($query) {
			$query->select('id', 'nombre');
		}])->with(['producto' => function ($query) {
			$query->select('id', 'origen', 'nombre');
		}])->select('id', 'sku', 'bultos', 'pcs_bulto', 'cantidad_total', 'deposito_lista_id')->where('id', $id)->first();


		//Lista deposito_detalle
		$lista_depo = DepositoLista::whereNot('id', $deposito_detalle->deposito_lista_id)
			->orderBy('nombre', 'desc')->get();

		$lista_depositos = [];
		foreach ($lista_depo as $destino) {
			array_push($lista_depositos, [
				'code' => $destino->id,
				'name' =>  $destino->nombre,
			]);
		}
		return response()->json([
			"lista_depositos" => $lista_depositos,
			"deposito_detalle" => $deposito_detalle,
		]);
	}

	public function showProductos(Request $request)
	{
		$productos = $request->input('productos');
		$origen_id = $request->input('origen_id');
		$detalle_productos = [];
		foreach ($productos as $key => $producto) {
			$deposito_detalle = DepositoProducto::with(['deposito_lista' => function ($query) {
				$query->select('id', 'nombre');
			}])
			/*->with(['producto' => function ($query) {
				$query->select('id', 'origen', 'nombre');
			}])*/
			->select('id', 'sku', 'bultos', 'pcs_bulto', 'cantidad_total', 'deposito_lista_id')->where('id', $producto['id'])->first();

			if (!empty($deposito_detalle)) {
				$nProduct=$this->productoService->ProductoBySku($deposito_detalle->sku);
				array_push($detalle_productos, [
					'id' => $deposito_detalle->id,
					'sku' => $deposito_detalle->sku,
					'bultos' => $deposito_detalle->bultos,
					'maxBultos' => $deposito_detalle->bultos,
					'pcs_bulto' => $deposito_detalle->pcs_bulto,
					//'nombre_producto' => $deposito_detalle->producto->nombre,
					'nombre_producto' => $nProduct->nombre??'',
				]);
			}
		}

		//Lista deposito_detalle
		$lista_depo = DepositoLista::whereNot('id', $origen_id)->orderBy('nombre', 'asc')->get();
		$lista_depositos = [];
		foreach ($lista_depo as $destino) {
			array_push($lista_depositos, [
				'code' => $destino->id,
				'name' =>  $destino->nombre,
			]);
		}


		return response()->json([
			"lista_depositos" => $lista_depositos,
			"detalle_productos" => $detalle_productos,
		]);
	}

	//detalle de excel importado a deposito
	public function show($id)
	{
		$deposito = Deposito::with(['depositos_detalles' => function ($query) {
			$query->select(
				'id',
				'sku',
				'pcs_bulto',
				'bultos',
				'cantidad_total',
				'deposito_id',
			)->with(['producto' => function ($query) {
				$query->select('id', 'nombre', 'codigo_barra', 'origen', 'imagen');
			}]);
		}])->select(
			'id',
			'nro_carpeta',
			'nro_contenedor',
			'estado',
			'fecha_arribado',
			'fecha_camino',
			DB::raw("DATE_FORMAT(created_at,'%d/%m/%y %H:%i:%s') AS fecha")
		)
			->where('id', $id)->first();

		return Inertia::render('Deposito/Show', [
			'deposito' => $deposito,
		]);
	}

	//actualizando importacion y actualizando deposito producto
	public function update(DepositoUpdateRequest $request, $id)
	{
		$importacion = Deposito::find($id);
		$estado = $request->estado;
		$usuario = auth()->user();
		DB::beginTransaction();
		try {

			if ($estado != $importacion->estado) {
				if ($estado == 'Arribado') {

					//moviendo a Deposito
					foreach ($importacion->depositos_detalles as $detalle) {
						$codigo = $detalle->sku;
						$producto = DepositoProducto::where('sku', '=', $codigo)
							->where('pcs_bulto', '=', $detalle->pcs_bulto)
							->where('deposito_lista_id', '=', 1)->first();
						if (empty($producto) || is_null($producto)) {
							//creando registro deposito
							$nuevo = [
								"sku" => $detalle->sku,
								"pcs_bulto" => $detalle->pcs_bulto,
								"bultos" => $detalle->bultos,
								"cantidad_total" => $detalle->bultos * $detalle->pcs_bulto,
								"deposito_lista_id" => 1
							];
							DepositoProducto::create($nuevo);
						} else {

							$nuevo_bulto = $producto->bultos + $detalle->bultos;
							$producto->update([
								"bultos" => $nuevo_bulto,
								"cantidad_total" => $nuevo_bulto * $detalle->pcs_bulto,
							]);
						}
					}
				}

				if ($estado == 'En camino') {

					//moviendo a Deposito
					foreach ($importacion->depositos_detalles as $detalle) {
						$codigo = $detalle->sku;
						$producto = DepositoProducto::where('sku', '=', $codigo)
							->where('pcs_bulto', '=', $detalle->pcs_bulto)
							->where('deposito_lista_id', '=', 1)->first();
						if (!empty($producto) || !is_null($producto)) {
							$nuevo_bulto = $producto->bultos - $detalle->bultos;
							$producto->update([
								"bultos" => $nuevo_bulto,
								"cantidad_total" => $nuevo_bulto * $detalle->pcs_bulto,
							]);
						}
					}
				}
			}

			$importacion->nro_carpeta = $request->nro_carpeta ?? '';
			$importacion->nro_contenedor = $request->nro_contenedor ?? '';
			$importacion->estado = $request->estado ?? '';
			$importacion->total = $request->total ?? 0;
			$importacion->fecha_arribado = $request->fecha_arribado ?? '';
			$importacion->fecha_camino = $request->fecha_camino ?? '';
			$importacion->user_id = $usuario->id;
			$importacion->save();


			DB::commit();

		} catch (Exception $e) {
			DB::rollBack();
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	//actualizar producto deposito
	public function updateProducto(DepositoImportacionUpdateRequest $request, $id)
	{
		$deposito_detalle = DepositoDetalle::find($id);

		//Guardando producto depositodetalle
		$deposito_detalle->update([
			"bultos" => $request->bultos,
			"pcs_bulto" => $request->pcs_bulto,
			"cantidad_total" => $request->cantidad_total,
		]);


		return Redirect::back();
	}

	//actualizar deposito
	public function updateDeposito(CambiarDepositoRequest $request, $id)
	{

		//buscando producto en deposito
		$datosOrigen = DepositoProducto::where('sku', $request->sku)
			->where('pcs_bulto', '=', $request->pcs_bulto)
			->where('deposito_lista_id', $request->origen_id)->first();
		$datosDestino = DepositoProducto::where('sku', $request->sku)
			->where('pcs_bulto', '=', $request->pcs_bulto)
			->where('deposito_lista_id', $request->destino_id)->first();

		DB::beginTransaction();
		try {

			$ne_pcs_bulto = $datosOrigen->pcs_bulto;
			$ne_bultos = $datosOrigen->bultos - $request->bultos;
			$da_producto = Producto::where('origen', '=', $request->sku)->first();
			$da_origen = DepositoLista::where('id', '=', $request->origen_id)->first();
			$da_destino = DepositoLista::where('id', '=', $request->destino_id)->first();
			$usuario = auth()->user();
			$datos_historial = [
				"sku" => $request->sku,
				"producto" => $da_producto->nombre,
				"bultos" => $request->bultos,
				"pcs_bulto" => $request->pcs_bulto,
				"origen" => $da_origen->nombre,
				"destino" => $da_destino->nombre,
				"usuario" => $usuario->name,
			];


			if (empty($datosDestino) || is_null($datosDestino)) {

				//quitando bultos del deposito de origen.
				$datosOrigen->update([
					"bultos" => $ne_bultos,
					"cantidad_total" => $ne_bultos * $ne_pcs_bulto,
				]);
				//creando registro deposito
				$nuevo = [
					"sku" => $datosOrigen->sku,
					"pcs_bulto" => $datosOrigen->pcs_bulto,
					"bultos" => $request->bultos,
					"cantidad_total" => $request->bultos * $datosOrigen->pcs_bulto,
					"deposito_lista_id" => $request->destino_id
				];
				DepositoProducto::create($nuevo);
			} else {
				$datosOrigen->update([
					"bultos" => $ne_bultos,
					"cantidad_total" => $ne_bultos * $ne_pcs_bulto,
				]);
				$nuevo_bulto = $datosDestino->bultos + $request->bultos;
				$datosDestino->update([
					"bultos" => $nuevo_bulto,
					"cantidad_total" => $nuevo_bulto * $datosOrigen->pcs_bulto,
				]);
			}
			DepositoHistorial::create([
				"datos" => json_encode($datos_historial),
			]);
			//guardando en tabla deposito historial

			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	//actualizar multiples productos deposito
	public function updateProductosDeposito(CambiarDepositoRequest $request)
	{

		$productos = $request->input('productos');
		$origen_id = $request->input('origen_id');
		$destino_id = $request->input('destino_id');
		foreach ($productos as $key => $producto) {
			//buscando producto en deposito
			$datosOrigen = DepositoProducto::where('sku', $producto['sku'])
				->where('pcs_bulto', '=', $producto['pcs_bulto'])
				->where('deposito_lista_id', $origen_id)->first();
			$datosDestino = DepositoProducto::where('sku', $producto['sku'])
				->where('pcs_bulto', '=', $producto['pcs_bulto'])
				->where('deposito_lista_id', $destino_id)->first();

			DB::beginTransaction();
			try {

				$ne_pcs_bulto = $datosOrigen->pcs_bulto;
				$ne_bultos = $datosOrigen->bultos - $producto['bultos'];
				$da_producto = Producto::where('origen', '=', $producto['sku'])->first();
				$da_origen = DepositoLista::where('id', '=', $origen_id)->first();
				$da_destino = DepositoLista::where('id', '=', $destino_id)->first();
				$usuario = auth()->user();
				$datos_historial = [
					"sku" => $producto['sku'],
					"producto" => $da_producto->nombre,
					"bultos" => $producto['bultos'],
					"pcs_bulto" => $producto['pcs_bulto'],
					"origen" => $da_origen->nombre,
					"destino" => $da_destino->nombre,
					"usuario" => $usuario->name,
				];


				if (empty($datosDestino) || is_null($datosDestino)) {

					//quitando bultos del deposito de origen.
					$datosOrigen->update([
						"bultos" => $ne_bultos,
						"cantidad_total" => $ne_bultos * $ne_pcs_bulto,
					]);
					//creando registro deposito
					$nuevo = [
						"sku" => $datosOrigen->sku,
						"pcs_bulto" => $datosOrigen->pcs_bulto,
						"bultos" =>  $producto['bultos'],
						"cantidad_total" =>  $producto['bultos'] * $datosOrigen->pcs_bulto,
						"deposito_lista_id" => $destino_id
					];
					DepositoProducto::create($nuevo);
				} else {
					$datosOrigen->update([
						"bultos" => $ne_bultos,
						"cantidad_total" => $ne_bultos * $ne_pcs_bulto,
					]);
					$nuevo_bulto = $datosDestino->bultos + $producto['bultos'];
					$datosDestino->update([
						"bultos" => $nuevo_bulto,
						"cantidad_total" => $nuevo_bulto * $datosOrigen->pcs_bulto,
					]);
				}
				DepositoHistorial::create([
					"datos" => json_encode($datos_historial),
				]);
				//guardando en tabla deposito historial

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

	//eliminar importacion
	public function destroy($id)
	{
		$importacion = Deposito::find($id);
		$estado = $importacion->estado;

		if ($estado == 'Arribado') {
		}
		if ($estado == 'En camino') {
		}

		$importacion->depositos_detalles()->delete();
		$importacion->delete();
	}

	//retirando definitamente del deposito
	public function destroyDeposito($id)
	{
		$deposito = DepositoProducto::find($id);
		$deposito->delete();
	}
	//retirando multiples productos definitamente del deposito
	public function destroyProductos(Request $request)
	{
		$productos = $request->input('productos');
		foreach ($productos as $key => $producto) {
			$deposito = DepositoProducto::find($producto['id']);
			if (!empty($deposito)) {
				$deposito->delete();
			}
		}
	}

	public function destroyDepositoLista(Request $request)
	{
		$id = $request->input('id');
		$deposito = DepositoLista::find($id);

		if (!empty($deposito)) {
			$deposito->depositos_productos()->delete();
			//$deposito->delete();
		}
	}

	public function exportExcel($id)
	{
		return Excel::download(new DepositoExport($id), 'DepositoExcel.xlsx');
	}
}
