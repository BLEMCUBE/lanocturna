<?php

namespace App\Imports;

use App\Models\ImportacionDetalle;
use App\Models\Producto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductoStockImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {
            if (!empty($row['SKU'])) {
                if (!is_null($row['CANTIDAD'])) {
                    $cantidad = $row['CANTIDAD'];
                } else {
                    $cantidad = 0;
                }
                $producto = Producto::where('origen', '=', $row['SKU'])->first();
                if (!is_null($producto)) {
                    $producto->update([
                        "stock" => intval($cantidad),
                        "stock_futuro" => intval($cantidad + $producto->en_camino)
                    ]);
                }
            }
        }
    }
}
