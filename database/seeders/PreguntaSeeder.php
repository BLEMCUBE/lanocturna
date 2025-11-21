<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class PreguntaSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$configuraciones =  [
			["type"=>"textarea","slug" => "pregunta-saludo", "key" => "Saludo inicial:", "value" => 'Hola,'],
			["type"=>"textarea", "slug" => "pregunta-firma", "key" => "Firma:", "value" => "Somos MERCADOLIDER IMPORTACIONES en nuestra nueva CASA CENTRAL zona CENTRO de Montevideo (próximo a colonia frente al ACU) con mas de 200m2 de SHOWROOM Y RETIRO en el mismo lugar. Nos encontramos de lunes a viernes de 10 a 18hs y los sábados de 09 a 13hs.Saludos, del equipo de MERCADOLIDER IMPORTACIONES!."],
		];

		//eliminar si existe
		//eliminando permisos
		foreach ($configuraciones as $configuracion) {
			Configuracion::where('slug', $configuracion['slug'])->delete();
		}

		//crear
		foreach ($configuraciones as $configuracion) {
			Configuracion::create($configuracion);
		}
	}
}
