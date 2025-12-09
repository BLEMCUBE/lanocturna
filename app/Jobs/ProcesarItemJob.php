<?php

namespace App\Jobs;

use App\Services\MercadoLibre\MercadoLibreService;
use App\Services\MercadoLibre\ItemService;

class ProcesarItemJob extends MercadoLibreBaseJob
{
	public function handle()
	{

		$service = new ItemService(app(MercadoLibreService::class));

		$service->storeNotificacion($this->resourceId, $this->clientId);
	}
}
