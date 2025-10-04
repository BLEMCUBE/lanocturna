<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Venta;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MercadoLibreImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    private $destino;
    private $usuario;
    public function  __construct($usuario, $destino)
    {
        $this->usuario = $usuario;
        $this->destino = $destino;
    }


    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {

            if (!empty($row['SKU'])) {


                if(!is_null($row['CLIENTE'])){
                    $cliente=json_encode(["nombre"=>$row['CLIENTE']]);
                }else{
                    $cliente=NULL;
                }

                //comprobando si existe
                $existe_nro_venta = Venta::where('nro_compra', '=', $row['NUMERO COMPRA'])->first();

                if (is_null($existe_nro_venta)) {

                    //creando venta
                    $venta = Venta::create([
                        'nro_compra' => $row['NUMERO COMPRA'],
                        'estado' => 'FACTURADO',
                        'destino' => $this->destino,
                        'moneda' => 'Pesos',
                        'tipo' => 'ENVIO',
                        'cliente' =>$cliente,
                        'vendedor_id' => $this->usuario->id,
                        'facturador_id' => $this->usuario->id,
                        'fecha_facturacion' => now()
                    ]);
                    $venta->update([
                        "codigo" => zero_fill($venta->id, 8)
                    ]);


                    $prod = Producto::where('origen', '=', $row['SKU'])->first();
                    $venta->detalles_ventas()->create(
                        [

                            "producto_id" => $prod->id,
                            "cantidad" =>  $row['CANTIDAD'],

                        ]
                    );
                    //actualizando stock producto
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock -  $row['CANTIDAD'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro" => $new_stock + $prod->en_camino
                    ]);
                }
            }
        }

    }
}
