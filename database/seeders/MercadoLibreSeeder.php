<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\MercadoLibreCliente;
use Illuminate\Database\Seeder;

class MercadoLibreSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$conf_mercado_libre =  [
			[
				"slug" => "mercadolibre-url-api",
				"key" => "Url Mercado Libre Api",
				"value" => 'https://api.mercadolibre.com'
			],

		];

		$mercado_libre_clientes =  [
			[
				"nombre" => "Tienda 1",
				"client_id" => "5958999118991163",
				"client_secret" => 'rZRg7x4k6qXMzMIM8YfWLM4smYUQtAIz',
				"is_default" =>1,
				"redirect_uri" =>  route('mercadolibre.callback')
			],

		];
		 // create permissions
        foreach ($mercado_libre_clientes as $item) {
            MercadoLibreCliente::create($item);
        }
		//crear configuracion
		/*		foreach ($conf_mercado_libre as $configuracion) {
			Configuracion::create($configuracion);
		}*/
	}
}
