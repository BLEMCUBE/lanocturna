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
			["titulo"=>"ENVIOS","tipo"=>"pregunta","descripcion" => "Hacemos envíos a todo el país y dentro de Montevideo mediante UES", "color" => "#ffb15e"],
			["titulo"=>"ORIGINAL","tipo"=>"pregunta","descripcion" => "Todos nuestros productos son originales", "color" => "#7ad690"],
			["titulo"=>"MEMORIAS","tipo"=>"pregunta","descripcion" => "Las memorias de 16gb tienen un costo de 14 dolares para una duracion de 2 dias, la de 32gb de 29 dolares para una duracion de 5 dias y 64gb tiene un costo de 34 dolares para 10 dias, Una vez que se llenan realiza el proceso de reciclaje,  Borran lo mas viejo y genera lo mas nuevo.", "color" => "#ff91d5"]
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
