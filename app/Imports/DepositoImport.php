<?php

namespace App\Imports;

use App\Models\DepositoDetalle;
use App\Models\DepositoProducto;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DepositoImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    private $deposito_id;
    private $estado;

    public function  __construct($deposito_id, $estado)
    {
        $this->deposito_id = $deposito_id;
        $this->estado = $estado;
    }

    //public function model(array $row)
    public function collection(Collection $rows)
    {



        foreach ($rows as $row) {

            if (!empty($row['sku'])) {
                DepositoDetalle::create([
                    "sku" => $row['sku'],
                    "pcs_bulto" => $row['pcs_bulto'],
                    "bultos" => $row['bultos'],
                    "cantidad_total" => $row['cantidad_total'],
                    "deposito_id" => $this->deposito_id
                ]);

                //buscando producto en deposito
                $producto = DepositoProducto::where('sku', '=', $row['sku'])
                    ->where('pcs_bulto', '=', $row['pcs_bulto'])
                    ->where('deposito_lista_id', '=', 1)->first();

                if ($this->estado == "Arribado") {
                    if (empty($producto) || is_null($producto)) {
                        DepositoProducto::create([
                            "sku" => $row['sku'],
                            "pcs_bulto" => $row['pcs_bulto'],
                            "bultos" => $row['bultos'],
                            "cantidad_total" => $row['cantidad_total'],
                            "deposito_lista_id" => 1
                        ]);
                    } else {
                        $new_pc_bulto = $producto->pcs_bulto; //  floatval($row['pcs_bulto']);
                        $new_bultos = $producto->bultos + floatval($row['bultos']);

                        $producto->update([
                            "pcs_bulto" => $new_pc_bulto,
                            "bultos" => $new_bultos,
                            "cantidad_total" => $new_bultos * $new_pc_bulto,
                        ]);
                    }
                }
            }
        }
    }
}
