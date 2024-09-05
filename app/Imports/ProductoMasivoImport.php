<?php

namespace App\Imports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductoMasivoImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!empty($row['SKU'])) {
               $datos=[
				"origen"=>$row['SKU'],
				"nombre"=>$row['NOMBRE'],
				"aduana"=>$row['ADUANA']??"ADUANA TEMPORAL",
				"codigo_barra"=>$row['CODIGO BARRA'],
				"stock"=>0,
				"stock_minimo"=>0,
				"imagen"=>"/images/productos/sin_foto.png"
			   ];
                $producto = Producto::where('origen', '=', $row['SKU'])
				->where('origen', '=', $row['CODIGO BARRA'])->first();
                if (is_null($producto)) {
					$new_producto = Producto::create($datos);
                }
            }
        }
    }
}
