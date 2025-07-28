<?php

namespace App\Imports;

use App\Models\CostoReal;
use App\Models\Importacion;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Carbon\Carbon;
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

		//$actual = Carbon::now()->format('Y-m-d');
		$f_arribo=Importacion::selectRaw("id,DATE_FORMAT(fecha_arribado ,'%Y-%m-%d') AS fecha")
		->where('id','=',$this->importacion_id)
		->first();

		foreach ($rows as $row) {
			if (!empty($row['sku'])) {

				if (!empty($row['costo_real'])) {
					$costo_real = round( $row['costo_real'],2);
				} else {
					$costo_real = 0;
				}
				$idDet=ImportacionDetalle::where('sku','=',$row['sku'])
				->where('importacion_id','=',$this->importacion_id)
				->select('id','sku')->first();

				$costo_real_reg = CostoReal::select('*')
				->where('sku','=',$row['sku'])
				->where('importacion_id','=',$this->importacion_id)
				->where('importaciones_detalle_id','=',$idDet->id);
				//->whereDate('fecha', '=', $f_arribo->fecha);

				if (!empty($costo_real_reg)) {
					$costo_real_reg->update([
						"monto" => $costo_real,
						"creador_id" => $this->creador_id,

					]);
				} else {
					$producto = Producto::select('id', 'origen')->where('origen', '=', $row['sku'])
						->first();
					CostoReal::create([
						"fecha" =>  $f_arribo->fecha,
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

}
