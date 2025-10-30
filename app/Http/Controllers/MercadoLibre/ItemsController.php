<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Services\MercadoLibreService;
use Illuminate\Support\Facades\Log;
use App\Services\ItemService;

class ItemsController extends Controller
{

	public function __construct(
		private MercadoLibreService $ml,
		private ItemService $itemService
	) {}

	//crear desde notificacion
	public function handles($payload)
	{
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;
		if (!$resource || !$userId) return;

		$item = $this->ml->apiGet($resource, $userId, []);
		$newItem=$this->itemService->updateOrCreate($item);
		if(!$newItem==null){
			Log::info("Item Creado [{$item['id']}]");
		}



	}
}
