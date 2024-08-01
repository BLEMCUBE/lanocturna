<?php

namespace App\Http\Controllers;

use App\Exports\ImportacionesExport;
use App\Exports\PagosImportacionesExport;
use App\Http\Requests\ImportacionUpdateRequest;
use App\Http\Requests\PagoImportacionStoreRequest;
use App\Http\Resources\PagoImportacionCollection;
use App\Models\Importacion;
use App\Models\ImportacionDetalle;
use App\Models\PagoImportacion;
use App\Models\Producto;
use Carbon\Carbon;
use Inertia\Inertia;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PagoImportacionController extends Controller
{
	public function __construct()
	{
		//protegiendo el controlador segun el rol
		//$this->middleware(['auth', 'permission:lista-importaciones'])->only('index');
		//$this->middleware(['auth', 'permission:crear-importaciones'])->only(['store']);
		//$this->middleware(['auth', 'permission:editar-importaciones'])->only(['update']);
		//$this->middleware(['auth', 'permission:eliminar-importaciones'])->only(['destroy']);
	}

	public function index()
	{
		return Inertia::render('PagosImportacion/Index', [

			'productos' => new PagoImportacionCollection(
				Importacion::with(['importaciones_pagos'])->orderBy('id', 'DESC')
					->get()
			)
		]);
	}


	public function store(PagoImportacionStoreRequest $request)
	{

		DB::beginTransaction();
		try {

			//creando importacion
			$importacion = PagoImportacion::create([
				'fecha_pago' => $request->fecha_pago ?? '',
				'banco' => $request->banco ?? '',
				'nro_transaccion' => $request->nro_transaccion ?? '',
				'monto' => $request->monto ?? 0,
				'importacion_id' => $request->id,
			]);

			DB::commit();
			return Redirect::route('pagos-importaciones.index')->with([
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

	public function showDetalle($id)
	{
		$importacion = Importacion::with(['importaciones_pagos'])->select('*')
			->where('id', $id)->first();
		return response()->json([
			"importacion" => $importacion
		]);
	}

	public function destroy($id)
	{
		$importacion = PagoImportacion::find($id);
		$importacion->delete();
	}

	public function exportExcel()
	{
		$datos = PagoImportacion::with(['importacion'])
		->select('*',DB::raw("DATE_FORMAT(fecha_pago,'%d/%m/%y') AS fecha"))->orderBy('id', 'DESC')
			->get();
		return Excel::download(new PagosImportacionesExport($datos), 'PagosImportaciones.xlsx');
	}
}
