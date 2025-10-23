<?php

namespace Database\Seeders;

use App\Models\RespuestaRapida;
use Illuminate\Database\Seeder;

class RespuestaRapidasSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$items =  [
			["titulo"=>"ENVIOS","descripcion" => "Hacemos envíos a todo el país y dentro de Montevideo mediante UES", "color" => "#ffb15e"],
			["titulo"=>"ORIGINAL","descripcion" => "Todos nuestros productos son originales", "color" => "#7ad690"]
		];

		//eliminar si existe
		foreach ($items as $item) {
			RespuestaRapida::where('titulo', $item['titulo'])->delete();
		}

		//crear
		foreach ($items as $item) {
			RespuestaRapida::create($item);
		}
	}
}
