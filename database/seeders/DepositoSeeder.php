<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deposito;

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
            ["nombre" => "DEPÓSITO VIRTUAL","descripcion"=>"Depósito en donde se registran todos bultos importados"],
            ["nombre" => "TIENDA","descripcion"=>"Tienda"],
        ];

        foreach ($depositos as $deposito) {
            Deposito::create($deposito);
        }
    }
}
