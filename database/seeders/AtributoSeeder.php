<?php

namespace Database\Seeders;

use App\Models\Atributo;
use Illuminate\Database\Seeder;

class AtributoSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$color = Atributo::create(['nombre' => 'Color']);
		$talla = Atributo::create(['nombre' => 'Talla']);

		$rojo = $color->valores()->create(['valor' => 'Rojo']);
		$azul = $color->valores()->create(['valor' => 'Azul']);
		$s = $talla->valores()->create(['valor' => 'S']);
		$m = $talla->valores()->create(['valor' => 'M']);
	}
}
