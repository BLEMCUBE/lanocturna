<?php

namespace App\Services;

use App\Models\MercadoLibrePregunta;
use App\Models\MercadoLibreCliente;
use App\Services\ItemService;
use App\Services\ListaUsuarioService;
use App\Services\MercadoLibreService;
use App\Models\MercadoLibreItem;
use Illuminate\Support\Facades\Log;
use App\Models\MercadoLibreListaUsuario;


class PreguntaService
{
	public function __construct(
		private ItemService $itemService,
		private MercadoLibreService $ml,
		private ListaUsuarioService $listaUsuarioService,
	) {}
	public function updateOrCreate($question)
	{
		$cliente = MercadoLibreCliente::with('usuario')->first();

		// item
		$existsItem = MercadoLibreItem::where('item_id', $question['item_id'] ?? null)->exists();
		if (!$existsItem) {
			$item = $this->ml->apiGet('/items/' . $question['item_id'], $cliente->usuario->meli_user_id);
			$this->itemService->updateOrCreate($item);
		}

		//usuario
		$existsUser = MercadoLibreListaUsuario::where('user_id', $question['from']['id'] ?? null)->exists();
		if (!$existsUser) {
			$itemUser = $this->ml->apiGet('/users/' . $question['from']['id'], $cliente->usuario->meli_user_id);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}

		$data =	MercadoLibrePregunta::updateOrCreate(
			['mercadolibre_pregunta_id' => $question['id']],
			[
				'item_id' => $question['item_id'],
				'seller_id' => $question['seller_id'],
				'text' => $question['text'],
				'status' => $question['status'],
				'date_created' => $question['date_created'],
				'from_user_id' => $question['from']['id'] ?? null,
				'payload' => $question,
			]
		);
		return $data;
	}
	public function storeNotificacion($payload)
	{
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;

		if (!$resource || !$userId) return;

		$question = $this->ml->apiGet($resource, $userId);
		//crear pregunta
		$newItem = $this->updateOrCreate($question);
		if ($newItem !== null) {
			Log::info("Pregunta registrada Notificacion [{$question['id']}]");
			//notificacion
			$this->ml->pusherNotificacion('ml', 'question');
		}
		$this->ml->actualizar($resource);
	}
}
