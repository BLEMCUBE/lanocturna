<?php

namespace App\Imports;

use App\Models\ImportacionDetalle;
use App\Models\Producto;
use App\Models\ProductoYuan;
//use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Jobs\ActualizarStockWooJob;

class ImportacionesImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
	private $importacion_id;
	private $estado;
	private $mueve_stock;
	private $yuan_id;
	public function  __construct($importacion_id, $estado, $mueve_stock, $yuan_id)
	{
		$this->importacion_id = $importacion_id;
		$this->estado = $estado;
		$this->mueve_stock = $mueve_stock;
		$this->yuan_id = $yuan_id;
	}

	//public function model(array $row)
	public function collection(Collection $rows)
	{

		foreach ($rows as $row) {

			if (!empty($row['sku'])) {
				ImportacionDetalle::create([
					"sku" => $row['sku'],
					"precio" => $row['precio'],
					"unidad" => $row['unidad'],
					"pcs_bulto" => $row['pcs_bulto'],
					"bultos" => $row['bultos'],
					"valor_total" => $row['valor_total'],
					"cantidad_total" => $row['cantidad_total'],
					"cbm_bulto" => $row['cbm_bulto'],
					"cbm_total" => $row['cbm_total'],
					"estado" => $this->estado ?? '',
					"mueve_stock" => $row['mueve_stock'] ?? '',
					"codigo_barra" => $row['codigo_barra'],
					"importacion_id" => $this->importacion_id
				]);

				$producto = Producto::where('origen', '=', $row['sku'])->first();

				if (!empty($producto)) {

					$stock_old = $producto->stock;
					if ($this->mueve_stock == 1) {
						if ($this->estado == "Arribado") {
							$old_arribado = $producto->arribado;
							if ($producto->stock == 0) {
								$stock_new = $producto->stock + floatval($row['cantidad_total']);
								$stock_futuro = $stock_new + $producto->en_camino;
							} else {

								if ($producto->stock < floatval($row['cantidad_total'])) {
									$stock_new = $producto->stock;
									$stock_futuro = $producto->stock_futuro;
								} else {
									$stock_new = $producto->stock + floatval($row['cantidad_total']);
									$stock_futuro = $stock_new + $producto->en_camino;
								}
							}

							$producto->update([
								"stock" => $stock_new,
								"arribado" => $old_arribado + floatval($row['cantidad_total']),
								"stock_futuro" => $stock_futuro,
							]);

							//actualizar stock web
							dispatch((new ActualizarStockWooJob($producto->origen, $stock_new))->onQueue('meli'));
						}

						if ($this->estado == "En camino") {
							$old_en_camino = $producto->en_camino;
							$producto->update([
								"en_camino" => $old_en_camino +  floatval($row['cantidad_total']),
								"stock_futuro" => $stock_old + $producto->en_camino + floatval($row['cantidad_total']),
							]);
						}
					}
					//registrando yuang
					//$producto->yuanes()->attach([$this->yuan_id]);
					ProductoYuan::create([
						"producto_id" => $producto->id,
						"tipo_cambio_yuan_id" => $this->yuan_id,
						"importacion_id" => $this->importacion_id
					]);
				}
			}
		}
	}
}
