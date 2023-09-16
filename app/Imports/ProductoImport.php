<?php

namespace App\Imports;

use App\Models\ImportacionDetalle;
use App\Models\Producto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductoImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    //public function model(array $row)
    public function collection(Collection $rows)
    {



        foreach ($rows as $row) {

            if (!empty($row['origen'])) {
                $producto = Producto::where('origen', '=', $row['origen'])->first();
                    $producto->update([
                        "stock" => intval($row['stock']),
                        "stock_futuro" => intval($row['stock_futuro']),
                    ]);


            }

        }
    }
}
