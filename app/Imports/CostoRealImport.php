<?php

namespace App\Imports;

use App\Models\CostoReal;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CostoRealImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
	private int $creador_id;
	private string $importacion_id;
	private string $hoy;



	public function  __construct($importacion_id, $hoy, $creador_id)
	{
		$this->creador_id = $creador_id;
		$this->importacion_id = $importacion_id;
		$this->hoy = $hoy;
	}


	public function collection(Collection $rows)
	{

		foreach ($rows as $row) {
			if (!empty($row['sku'])) {

				if (!is_null($row['costo_real'])) {
					$costo_real =  $row['costo_real'];
				} else {
					$costo_real = 0;
				}
				$idDet=ImportacionDetalle::where('sku','=',$row['sku'])
				->where('importacion_id','=',$this->importacion_id)
				->select('id')->first();
				$costo_real_reg = CostoReal::select('*')
				->where('sku','=',$row['sku'])
				->where('importacion_id','=',$this->importacion_id)
				->where('importaciones_detalle_id','=',$idDet->id)
				->whereDate('fecha', '=', $this->hoy)->first();

				if (!is_null($costo_real_reg)) {
					$costo_real_reg->update([
						"monto" => $costo_real,
						"creador_id" => $this->creador_id,

					]);
				} else {
					$producto = Producto::select('id', 'origen')->where('origen', '=', $row['sku'])
						->first();
					CostoReal::create([
						"fecha" => $this->hoy,
						"sku" => $row['sku'],
						"origen" => 'IMPORTACION',
						"monto" => $costo_real,
						"producto_id" => $producto->id,
						"importacion_id" => $this->importacion_id,
						"importaciones_detalle_id" => $idDet->id,
						"creador_id" => $this->creador_id
					]);
				}
			}
		}
	}
	/*
	public function collection(Collection $rows)
    {

        foreach ($rows as $row) {
            if (!empty($row['sku'])) {
                if (!is_null($row['costo_real'])) {
                    $costo_real =  $row['costo_real'];
                } else {
                    $costo_real = 0;
                }
				$producto = ImportacionDetalle::where('sku', '=', $row['sku'])
				->where('importacion_id', '=', $this->importacion_id)
				->first();
                if (!is_null($producto)) {
                    $producto->update([
                        "costo_real" => $costo_real,

                    ]);
                }
            }
        }
    }*/
}
