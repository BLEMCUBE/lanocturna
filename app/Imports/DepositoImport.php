<?php

namespace App\Imports;

use App\Models\DepositoDetalle;
use App\Models\DepositoProducto;
use App\Models\Producto;
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
                    "unidad" => $row['unidad'],
                    "pcs_bulto" => $row['pcs_bulto'],
                    "bultos" => $row['bultos'],
                    "cantidad_total" => $row['cantidad_total'],
                    "estado" => $this->estado ?? '',
                    "codigo_barra" => $row['codigo_barra'],
                    "deposito_id" => $this->deposito_id
                ]);

                //buscando producto en deposito
                $producto = DepositoProducto::where('sku', '=', $row['sku'])
                ->where('deposito_lista_id', '=',1)->first();

                if ($this->estado == "Arribado") {
                if(empty($producto) || is_null($producto)){
                    DepositoProducto::create([
                        "sku" => $row['sku'],
                        "unidad" => $row['unidad'],
                        "pcs_bulto" => $row['pcs_bulto'],
                        "bultos" => $row['bultos'],
                        "cantidad_total" => $row['cantidad_total'],
                        "codigo_barra" => $row['codigo_barra'],
                        "deposito_lista_id" => 1
                    ]);

                    //dd( 'crear');
                }else{
                    $producto->update([
                        "pcs_bulto" => $producto->pcs_bulto+  floatval($row['pcs_bulto']),
                        "bultos" => $producto->bultos + floatval($row['bultos']),
                        "cantidad_total" => $producto->cantidad_total + floatval($row['cantidad_total']),
                    ]);
                    //dd( 'actualizar');
                }
            }
            }



        }
    }
}
