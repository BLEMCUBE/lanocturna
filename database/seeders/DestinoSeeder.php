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
            ["nombre" => "UES WEB"],
            ["nombre" => "FLEX"],
            ["nombre" => "MERCADOLIBRE"],
            ["nombre" => "SALON"],
            ["nombre" => "UES"],
            ["nombre" => "WEB"],
        ];

        foreach ($destinos as $destino) {
            Destino::create($destino);
        }
    }
}
