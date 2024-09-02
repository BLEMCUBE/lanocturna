<?php

namespace App\Http\Controllers;

use App\Exports\PagosServiciosExport;
use App\Http\Requests\PagoServicioStoreRequest;
use App\Http\Requests\PagoServicioUpdateRequest;
use App\Http\Resources\PagoServicioCollection;
use App\Models\ConceptoPago;
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
		//protegiendo el controlador segun el rol
		//$this->middleware(['auth', 'permission:lista-pagoservicio'])->only('index'); pagoservicio
		//$this->middleware(['auth', 'permission:crear-pagoservicio'])->only(['store']);
		//$this->middleware(['auth', 'permission:editar-pagoservicio'])->only(['update']);
		//$this->middleware(['auth', 'permission:eliminar-pagoservicio'])->only(['destroy']);
	}

	public function index()
	{
		return Inertia::render('PagosServicio/Index', [

			'items' => new PagoServicioCollection(
				PagoServicio::with(['concepto_pago'])
					->with(['usuario'])
					->when(Request::input('inicio'), function ($query) {
						$query->whereDate('fecha_pago', '>=', Request::input('inicio'));
					})
					->when(Request::input('fin'), function ($query) {
						$query->whereDate('fecha_pago', '<=', Request::input('fin'));
					})
					->orderBy('id', 'DESC')
					->get()
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
				'nro_factura' => $request->nro_factura ?? '',
				'monto' => $request->monto ?? '',
				'observacion' => $request->observacion ?? '',
				'user_id' => $usuario->id ?? '',

			]);

			DB::commit();
			return Redirect::route('pago-servicio.index')->with([
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

	public function exportExcel()
	{
		$datos = DB::table('pagos_servicios as pa')
			->join('concepto_pago as cp', 'cp.id', '=', 'pa.concepto_pago_id')
			->select(
				'pa.monto',
				'pa.moneda',
				'pa.nro_factura',
				'cp.nombre as concepto',
				'pa.observacion',
				DB::raw("DATE_FORMAT(pa.fecha_pago,'%d/%m/%y') AS fecha")
			)
			->when(Request::input('inicio'), function ($query) {
				$query->whereDate('pa.fecha_pago', '>=', Request::input('inicio'));
			})
			->when(Request::input('fin'), function ($query) {
				$query->whereDate('pa.fecha_pago', '<=', Request::input('fin'));
			})
			->orderByRaw('pa.fecha_pago desc')
			->get();
		return Excel::download(new PagosServiciosExport($datos), 'PagosServicios.xlsx');
	}
}
