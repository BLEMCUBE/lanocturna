<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MercadoLibreNotificacion;
use App\Services\MercadoLibreService;
use App\Services\PreguntaService;
use Illuminate\Support\Facades\Log;
use App\Services\MLVentaService;

class OrdersController extends Controller
{

	public function __construct(
		private	MercadoLibreService $ml,
		private PreguntaService $preguntaService,
		private MLVentaService $mLVentaService,
	) {}


	public function storeNotificacion($payload)
	{
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;

		if (!$resource || !$userId) return;

		$question = $this->ml->apiGet($resource, $userId);

		//crear
		$order = $this->mLVentaService->updateOrCreate($question);
		if ($order !== null) {
			Log::info("Orden registrada Notificacion [{$question['id']}]");
		}
		$this->actualizar($resource);
	}

	private function actualizar($resource){
		$notif = MercadoLibreNotificacion::where('resource', '=', $resource)->first();
			if (!is_null($notif)) {
				$notif->update([
					'status' => 'processed'
				]);
			}

	}
}
