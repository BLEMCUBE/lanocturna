<?php

namespace App\Services\MercadoLibre;

use App\Models\MLPregunta;
use App\Models\MLApp;
use App\Models\MLItem;
use Illuminate\Support\Facades\Log;
use App\Models\MLListaUsuario;
use App\Models\MLRespuesta;
use App\Traits\BaseMLService;

class PreguntaService
{
	use BaseMLService;
	public function __construct(
		private ItemService $itemService,
		private MercadoLibreService $ml,
		private UsuarioService $mLUsuarioService,
		private ListaUsuarioService $listaUsuarioService,
	) {}
	public function updateOrCreate($question)
	{
		$meli_user_id = $this->usuarioMeliId();

		// item
		$existsItem = MLItem::where('item_id', $question['item_id'] ?? null)->exists();
		if (!$existsItem) {
			$item = $this->mlForClient()->apiGet('/items/' . $question['item_id'], $meli_user_id);
			$this->itemService->updateOrCreate($item);
		}

		//usuario
		$existsUser = MLListaUsuario::where('user_id', $question['from']['id'] ?? null)->exists();
		if (!$existsUser) {
			$itemUser = $this->mlForClient()->apiGet('/users/' . $question['from']['id'], $meli_user_id);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}

		$data =	MLPregunta::updateOrCreate(
			['pregunta_id' => $question['id']],
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

		if (!is_null($question['answer'])) {
			MLRespuesta::updateOrCreate(
				['pregunta_id' => $question['id']],
				[
					'pregunta_id' => $question['id'],
					'from_user_id' => $question['from']['id'] ?? null,
					'date_created' => $question['answer']['date_created'],
					'text' => $question['answer']['text'],
					'payload' => $question['answer']
				]
			);
		}
		return $data;
	}
	public function storeNotificacion($payload)
	{
		$appId = $payload['application_id'] ?? null;

		if (! $appId) {
			Log::warning('MensajeService sin application_id', $payload);
			return;
		}
		$this->forClient($appId);
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;
		if (!$resource || !$userId) return;

		$question = $this->mlForClient()->apiGet($resource, $userId);

		$newItem = $this->updateOrCreate($question);
		if ($newItem !== null) {
			Log::info("Pregunta registrada Notificacion [{$question['id']}]");
		}
		$this->ml->actualizar($resource);
	}


	public function getPorItem($itemId,$clientId)
	{
		$offset = 0;
		$limit = 50;

		$parametros = [
			'api_version' => 4,
			'item' => $itemId,
			'offset' => $offset,
			'limit' => $limit,
		];
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;
		do {
			$response = $this->mlForClient()->apiGet("/questions/search", $meli_user_id, $parametros);
			$messages = $response['questions'] ?? [];

			foreach ($messages as $msg) {
				$this->updateOrCreate($msg);
			}

			// Paginación
			$offset += $limit;
			$total = $response['paging']['total'] ?? $offset;
		} while ($offset < $total);
	}

	public function getSinLeer()
	{
		$datos = [];
		$clientes = MLApp::with('usuario')->whereHas('usuario')->get();

		foreach ($clientes as $key => $value) {
			$query = MLPregunta::where('status', '=', 'UNANSWERED')->with('from_user')
				->with('item')->whereHas('item', function ($query) {
					$query->where('status', 'active');
				})
				->where('seller_id', '=', $value->usuario->meli_user_id)->count();

			array_push($datos, [
				'client_id' => $value->app_id,
				'cantidad' => $query,
			]);
		}
		return $datos;
	}
}
