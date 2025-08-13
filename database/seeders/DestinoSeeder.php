<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Destino;

class DestinoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $destinos =  [
            ["nombre" => "CADETERIA"],
            ["nombre" => "FLEX"],
            ["nombre" => "MERCADOLIBRE"],
            ["nombre" => "RETIROS WEB"],
            ["nombre" => "SALON"],
            ["nombre" => "UES"],
            ["nombre" => "UES WEB"],
            ["nombre" => "ENVIO FLASH"],
        ];

        foreach ($destinos as $destino) {
            Destino::create($destino);
        }
    }
}
