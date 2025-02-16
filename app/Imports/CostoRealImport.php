<?php

namespace App\Imports;

use App\Models\ImportacionDetalle;
use App\Models\Producto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CostoRealImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
	private $importacion_id;


    public function  __construct($importacion_id)
    {
        $this->importacion_id = $importacion_id;

    }


    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {
            if (!empty($row['sku'])) {
                if (!is_null($row['costo_real'])) {
                    $costo_real = number_format((float)$row['costo_real'],2);
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
    }
}
