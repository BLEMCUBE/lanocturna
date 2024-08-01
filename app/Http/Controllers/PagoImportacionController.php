<?php

namespace App\Http\Controllers;

use App\Exports\ImportacionesExport;
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

	public function update(ImportacionUpdateRequest $request, $id)
	{
		$importacion = Importacion::find($id);
		$estado = $request->estado;

		DB::beginTransaction();
		try {
			if ($estado != $importacion->estado) {
				if ($estado == 'Arribado') {
					//creando detalle importacion
					foreach ($importacion->importaciones_detalles as $detalle) {
						$codigo = $detalle->sku;
						$producto = Producto::where('origen', '=', $codigo)
							->first();
						$new_encamino = $producto->en_camino - $detalle->cantidad_total;
						$new_arribado = $producto->arribado + $detalle->cantidad_total;
						$new_stock = $producto->stock + $detalle->cantidad_total;
						$new_futuro = $new_stock + $new_encamino;
						$producto->update([
							"stock" => $new_stock,
							"arribado" => $new_arribado,
							"en_camino" => $new_encamino,
							"stock_futuro" => $new_futuro,
						]);

						//actualizando importacion detall
						$impor_detall = ImportacionDetalle::where('importacion_id', '=', $importacion->id);
						$impor_detall->update([
							"estado" => $estado,
						]);
					}
				}
				if ($estado == 'En camino') {
					//actualizando stock productos
					foreach ($importacion->importaciones_detalles as $detalle) {
						$codigo = $detalle->sku;
						$producto = Producto::where('origen', '=', $codigo)
							->first();

						$new_arribado = $producto->arribado - $detalle->cantidad_total;
						$new_encamino = $producto->en_camino + $detalle->cantidad_total;
						$new_stock = $producto->stock - $detalle->cantidad_total;
						$new_futuro = $new_stock + $new_encamino;

						$producto->update([
							"en_camino" => $new_encamino,
							"stock" => $new_stock,
							"arribado" => $new_arribado,
							"stock_futuro" => $new_futuro,
						]);

						//actualizando importacion detall
						$impor_detall = ImportacionDetalle::where('importacion_id', '=', $importacion->id);
						$impor_detall->update([
							"estado" => $estado,
						]);
					}
				}
			}

			$importacion->nro_carpeta = $request->input('nro_carpeta');
			$importacion->nro_contenedor = $request->input('nro_contenedor');
			$importacion->estado = $request->input('estado');
			$importacion->fecha_arribado     = $request->input('fecha_arribado');
			$importacion->fecha_camino = $request->input('fecha_camino');
			$importacion->save();

			DB::commit();
			/*return Redirect::route('importaciones.index')->with([
                // 'success' =>  $venta->codigo
            ]);*/
		} catch (Exception $e) {
			DB::rollBack();
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	public function destroy($id)
	{
		$importacion = PagoImportacion::find($id);
		$importacion->delete();
	}

	public function exportExcel($id)
	{
		return Excel::download(new ImportacionesExport($id), 'PagosImportacionExcel.xlsx');
	}
}
