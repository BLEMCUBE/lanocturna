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
            ["slug"=>"codigo-maestro","key" => "Código maestro","value"=>'$2y$10$gOv7GFhR3En3.bGp2AJsJeieA1O/SkPiliI8L2ArGSA6qs1PgnLxW'], //123456
            ["slug"=>"direccion-tienda","key" => "Dirección Tienda","value"=>"COLONIA 1280"],
        ];

        foreach ($configuraciones as $configuracion) {

            Configuracion::create($configuracion);
        }
    }
}
