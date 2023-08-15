<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;
use App\Models\Longitud;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configuraciones =  [
            ["slug"=>"impuesto-iva","key" => "Impuesto IVA %","value"=>"22"],
        ];

        foreach ($configuraciones as $configuracion) {

            Configuracion::create($configuracion);
        }
    }
}
