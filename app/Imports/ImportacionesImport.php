<?php

namespace App\Imports;

use App\Models\DepositoDetalle;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
//use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

//class ImportacionesImport implements ToModel ,WithHeadingRow,WithCalculatedFormulas
class ImportacionesImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    private $importacion_id;
    private $estado;
    private $mueve_stock;
    public function  __construct($importacion_id, $estado,$mueve_stock)
    {
        $this->importacion_id = $importacion_id;
        $this->estado = $estado;
        $this->mueve_stock = $mueve_stock;
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

                DepositoDetalle::create([
                    "sku" => $row['sku'],
                    "precio" => $row['precio'],
                    "unidad" => $row['unidad'],
                    "pcs_bulto" => $row['pcs_bulto'],
                    "bultos" => $row['bultos'],
                    "valor_total" => $row['valor_total'],
                    "cantidad_total" => $row['cantidad_total'],
                    "cbm_bulto" => $row['cbm_bulto'],
                    "cbm_total" => $row['cbm_total'],
                    "codigo_barra" => $row['codigo_barra'],
                    "importacion_id" => $this->importacion_id,
                    "deposito_id" => 1
                ]);


                $producto = Producto::where('codigo_barra', '=', $row['codigo_barra'])->first();

                if ($this->mueve_stock ==true) {
                if ($this->estado == "Arribado") {

                    $producto->update([
                        "stock" => $producto->stock + floatval($row['cantidad_total'])
                    ]);
                }
                if ($this->estado == "En camino") {
                    $producto->update([
                        "stock_futuro" => $producto->stock_futuro + floatval($row['cantidad_total'])
                    ]);
                }
            }
            }

        }
    }
}
