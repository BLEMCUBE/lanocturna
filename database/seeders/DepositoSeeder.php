<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DepositoLista;

class DepositoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $depositos =  [
            ["nombre" => "DEPÓSITO TEMPORAL","descripcion"=>"Depósito en donde se registran todos bultos importados"],
            ["nombre" => "TIENDA","descripcion"=>"Tienda"],
        ];

        foreach ($depositos as $deposito) {
            DepositoLista::create($deposito);
        }
    }
}
