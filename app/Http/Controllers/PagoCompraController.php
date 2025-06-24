<?php

namespace App\Http\Controllers;

use App\Exports\PagosComprasExport;
use App\Http\Requests\PagoCompraStoreRequest;
use App\Http\Requests\PagoServicioUpdateRequest;
use App\Http\Resources\PagoCompraCollection;
use App\Models\Compra;
use App\Models\ConceptoPago;
use App\Models\MetodoPago;
use App\Models\PagoCompra;
use App\Models\PagoServicio;
use Inertia\Inertia;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class PagoCompraController extends Controller
{
	public function __construct()
	{
		//protegiendo el controlador segun el rol
		//$this->middleware(['auth', 'permission:lista-pagocompra'])->only('index'); pagocompra
		//$this->middleware(['auth', 'permission:crear-pagocompra'])->only(['store']);
		//$this->middleware(['auth', 'permission:editar-pagocompra'])->only(['update']);
		//$this->middleware(['auth', 'permission:eliminar-pagocompra'])->only(['destroy']);
	}

	public function index()
	{

		return Inertia::render('PagosCompra/Index', [

			'productos' => new PagoCompraCollection(
				Compra::with(['compra_pagos'])->orderBy('id', 'DESC')
					->get()
			)
		]);
	}
	public function showDetalle($id)
	{
		$importacion = Compra::with(['compra_pagos'])->select('*')
			->where('id', $id)->first();
		return response()->json([
			"importacion" => $importacion
		]);
	}

	public function store(PagoCompraStoreRequest $request)
	{

		DB::beginTransaction();
		try {

			//creando importacion

			$importacion = PagoCompra::create([
				'fecha_pago' => $request->fecha_pago ?? '',
				'banco' => $request->banco ?? '',
				'moneda' => $request->moneda ?? '',
				'nro_transaccion' => $request->nro_transaccion ?? '',
				'monto' => $request->monto ?? 0,
				'compra_id' => $request->id,
			]);


			$compra = Compra::select('total', 'pagado', 'id')->where('id', '=', $request->id)->get();
			$pagos = PagoCompra::select('compra_id', 'monto')->where('compra_id', '=', $request->id)
				->sum('monto');

			if ($pagos == $compra[0]->total) {
				$compra[0]->update([
					"pagado" => 1
				]);
			} else {
				$compra[0]->update([
					"pagado" => 0
				]);
			}

			DB::commit();
			return Redirect::route('pagos-compras.index')->with([
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
		$importacion = PagoCompra::find($id);
		$importacion->delete();
		$compra = Compra::select('total', 'pagado', 'id')->where('id', '=', $importacion->compra_id)->get();
		$pagos = PagoCompra::select('compra_id', 'monto')->where('compra_id', '=', $importacion->compra_id)
			->sum('monto');

		if ($pagos == $compra[0]->total) {
			$compra[0]->update([
				"pagado" => 1
			]);
		} else {
			$compra[0]->update([
				"pagado" => 0
			]);
		}
	}

	public function exportExcel()
	{
		$datos = DB::table('pagos_compras as pa')
			->join('compras as im', 'im.id', '=', 'pa.compra_id')
			->select(
				'im.nro_factura',
				'pa.monto',
				'pa.banco',
				'pa.nro_transaccion',
				DB::raw("DATE_FORMAT(pa.fecha_pago,'%d/%m/%y') AS fecha")
			)
			->orderByRaw('pa.fecha_pago - im.nro_factura desc')
			->get();
		return Excel::download(new PagosComprasExport($datos), 'PagosCompras.xlsx');
	}
}
