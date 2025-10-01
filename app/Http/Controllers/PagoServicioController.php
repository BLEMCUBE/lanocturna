<?php

namespace App\Http\Controllers;

use App\Exports\PagosServiciosExport;
use App\Http\Requests\PagoServicioStoreRequest;
use App\Http\Requests\PagoServicioUpdateRequest;
use App\Http\Resources\PagoServicioCollection;
use App\Models\ConceptoPago;
use App\Models\MetodoPago;
use App\Models\PagoServicio;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class PagoServicioController extends Controller
{
	public function __construct()
	{

	}

	public function index()
	{
		$conceptos = ConceptoPago::orderBy('nombre', 'ASC')->get();
		$lista_categorias = [];
		foreach ($conceptos as $value) {
			array_push($lista_categorias, [
				'value' => $value->id,
				'label' =>  $value->nombre,
			]);
		}
		return Inertia::render('PagosServicio/Index', [
			'conceptos' => $lista_categorias,
			'filtro' => Request::only(['conceptos']),
			'items' => new PagoServicioCollection(
				PagoServicio::query()
					->select(
						'pagos_servicios.id',
						'pagos_servicios.monto',
						'pagos_servicios.moneda',
						'pagos_servicios.nro_factura',
						'pagos_servicios.observacion',
						'pagos_servicios.metodo_pago_id',
						'cp.nombre as tconcepto',
						//'mp.nombre as tmetodo',
						DB::raw("DATE_FORMAT(pagos_servicios.fecha_pago,'%d/%m/%y') AS fecha")
					)
					->join('concepto_pago as cp', 'cp.id', '=', 'pagos_servicios.concepto_pago_id')
					->with(['concepto_pago' => function ($query) {
						$query->select(DB::raw("id,nombre"))->orderBy('nombre', 'ASC');
					}])
					->with(['metodo_pago' => function ($query) {
						$query->select(DB::raw("id,nombre"))->orderBy('nombre', 'ASC');
					}])
					->when(Request::input('inicio'), function ($query) {
						$query->whereDate('pagos_servicios.fecha_pago', '>=', Request::input('inicio'));
					})
					->when(Request::input('fin'), function ($query) {
						$query->whereDate('pagos_servicios.fecha_pago', '<=', Request::input('fin'));
					})
					->when(Request::input('concepto'), function ($query) {
						$query->whereHas('concepto_pago', function ($query) {
							$query->whereIn('id', Request::input('concepto'));
						});
					})->orderBy('id', 'DESC')->get()
			)
		]);
	}

	public function store(PagoServicioStoreRequest $request)
	{
		$usuario = auth()->user();
		DB::beginTransaction();
		try {
			//creando Pago
			$item = PagoServicio::create([
				'fecha_pago' => $request->fecha_pago ?? '',
				'moneda' => $request->moneda ?? '',
				'concepto_pago_id' => $request->concepto_pago_id ?? '',
				'metodo_pago_id' => $request->metodo_pago_id ?? '',
				'nro_factura' => $request->nro_factura ?? '',
				'monto' => $request->monto ?? '',
				'observacion' => $request->observacion ?? '',
				'user_id' => $usuario->id ?? '',

			]);

			DB::commit();
			return Redirect::route('pago-servicio.index')->with([]);
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
		$item = PagoServicio::select('*')
			->where('id', $id)->first();
		return response()->json([
			"item" => $item
		]);
	}

	public function destroy($id)
	{
		$item = PagoServicio::find($id);
		$item->delete();
	}

	public function update(PagoServicioUpdateRequest $request, $id)
	{
		$item = PagoServicio::find($id);
		$item->update($request->validated());
	}

	public function conceptos()
	{
		$items = ConceptoPago::get();

		$lista_conceptos = [];
		foreach ($items as $destino) {
			array_push($lista_conceptos, [
				'code' => $destino->id,
				'name' =>  $destino->nombre,
			]);
		}
		return response()->json([
			"conceptos" => $lista_conceptos
		]);
	}

	public function metodos()
	{
		$items = MetodoPago::get();

		$lista_metodos = [];
		foreach ($items as $destino) {
			array_push($lista_metodos, [
				'code' => $destino->id,
				'name' =>  $destino->nombre,
			]);
		}
		return response()->json([
			"metodos" => $lista_metodos
		]);
	}

	public function exportExcel()
	{
		$datos = PagoServicio::query()
			->select(
				'pagos_servicios.monto',
				'pagos_servicios.moneda',
				'pagos_servicios.nro_factura',
				'pagos_servicios.observacion',
				'pagos_servicios.metodo_pago_id',
				'cp.nombre as tconcepto',
				DB::raw("DATE_FORMAT(pagos_servicios.fecha_pago,'%d/%m/%y') AS fecha")
			)
			->join('concepto_pago as cp', 'cp.id', '=', 'pagos_servicios.concepto_pago_id')
			->with(['concepto_pago' => function ($query) {
				$query->select(DB::raw("id,nombre"))->orderBy('nombre', 'DESC');
			}])
			->with(['metodo_pago' => function ($query) {
				$query->select(DB::raw("id,nombre"))->orderBy('nombre', 'ASC');
			}])
			->when(Request::input('inicio'), function ($query) {
				$query->whereDate('pagos_servicios.fecha_pago', '>=', Request::input('inicio'));
			})
			->when(Request::input('fin'), function ($query) {
				$query->whereDate('pagos_servicios.fecha_pago', '<=', Request::input('fin'));
			})
			->when(Request::input('concepto'), function ($query) {
				$query->whereHas('concepto_pago', function ($query) {
					$query->whereIn('id', Request::input('concepto'));
				});
			})->orderBy('pagos_servicios.id', 'DESC')->get();

		return Excel::download(new PagosServiciosExport($datos), 'PagosServicios.xlsx');
	}
}
