<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class PusherSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		$configuraciones = array(
			array(
				"slug" => "pusher-id",
				"key" => "Pusher id",
				"value" => "2020881"
			),
			array(
				"slug" => "pusher-key",
				"key" => "Pusher key",
				"value" => "4c2072de0238dd3ea6b9"
			),
			array(

				"slug" => "pusher-secret",
				"key" => "Pusher secret",
				"value" => "b19c54aa43395728f552"
			),
			array(
				"slug" => "pusher-cluster",
				"key" => "Pusher cluster",
				"value" => "us2"
			),
			array(

				"slug" => "pusher-forcetls",
				"key" => "Pusher forceTLS",
				"value" => "false"
			),
			array(
				"slug" => "url-tienda",
				"key" => "Url Tienda",
				"value" => "https://mercadolider.com.uy"
			),
		);



		foreach ($configuraciones as $configuracion) {

			Configuracion::create($configuracion);
		}
	}
}
