<?php

namespace App\Services\MercadoLibre;

use App\Models\MLApp;

class MLAppService
{
	public function getNombre($client_id)
	{
		$tienda = MLApp::where('app_id', $client_id)->select('nombre')->first();
		return $tienda->nombre ?? '';
	}
}
