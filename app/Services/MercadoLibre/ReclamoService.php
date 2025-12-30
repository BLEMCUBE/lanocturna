<?php

namespace App\Services\MercadoLibre;

use App\Models\MLApp;
use App\Models\MLOrden;
use App\Models\MLReclamo;
use App\Models\MLReclamoAccion;
use App\Models\MLReclamoMensaje;
use App\Traits\BaseMLService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Helpers\MercadoLibreClaimHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ReclamoService
{
	use BaseMLService;

	public function __construct(
		private MercadoLibreService $ml,
		private ItemService $itemService,
		private UsuarioService $mLUsuarioService
	) {}

	/**
	 * Crear o actualizar reclamo
	 */
	public function updateOrCreate($item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;

		// Guardar reclamo
		$reclamo = MLReclamo::updateOrCreate(
			['reclamo_id' => $item['id']],
			[
				'meli_user_id'   => $this->clienteId(),
				'reclamo_id' => $item['id'],
				'resource'     => $item['resource'] ?? null,
				'type'     => $item['type'] ?? null,
				'stage'     => $item['stage'] ?? null,
				'resource_id'    => $item['resource_id'] ?? null,
				'reason'   => $item['resolution']['reason'] ?? null,
				'status'      => $item['status'] ?? 'opened',
				'reason_id'   => $item['reason_id'] ?? null,
				'date_created'     =>  $item['date_created'] ?? null,
				'last_updated'     =>  $item['last_updated'] ?? null,
				'payload'     => $item,
			]
		);
		// consultar orden por por orden_id
		if ($item['resource'] == 'order') {
			$exist = MLOrden::where('orden_id', '=', $item['resource_id'])->first();
			if ($exist === null) {

				$orden = $this->mlForClient()->apiGetDos('/orders/' . $item['resource_id'], $meli_user_id);

				if (!$orden['success']) {
					// Lanzamos excepción para forzar reintento
					throw new \Exception("Error ML ({$orden['status_code']}): " . json_encode($orden['body']));
				}

				// Guardamos o actualizamos la venta
				app(OrdenService::class)->updateOrCreate($orden['body'], $this->clienteId());
			}
		}

		//detalle
		$det = $this->mlForClient()->apiGetDos('/post-purchase/v1/claims/' . $item['id'] . '/detail', $meli_user_id);
		if ($det['success']) {
			$det = MLReclamo::updateOrCreate(
				['reclamo_id' => $item['id']],
				[
					'detalle'     => $det['body'],
				]
			);
		}

		//motivo
		/*
		$motivos = $this->mlForClient()->apiGetDos('/post-purchase/v1/claims/reasons/' . $item['reason_id'], $meli_user_id);
		if ($motivos['success']) {
			$reclamo = MLReclamo::updateOrCreate(
				['reclamo_id' => $item['id']],
				[
					'motivos'     => $motivos['body'],
				]
			);
		}
		*/

		//reputacion
		$reputacion = $this->mlForClient()->apiGetDos('/post-purchase/v1/claims/' . $item['id'] . '/affects-reputation', $meli_user_id);
		if ($reputacion['success']) {
			$reclamo = MLReclamo::updateOrCreate(
				['reclamo_id' => $item['id']],
				[
					'reputacion'     => $reputacion['body'],
				]
			);
		}

		//mensajes
		$this->mensajes($item['id'], $clientId);

		return $reclamo;
	}

	public function updateOrCreateAcciones($rId, $item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;

		$reclamo = MLReclamoAccion::updateOrCreate(
			['reclamo_id' => $rId],
			[
				'reclamo_id' => $rId,
				'date' => now(),
				'payload'     => $item,
			]
		);


		return $reclamo;
	}


	/**
	 * Notificación desde Mercado Libre
	 */
	public function storeNotificacion($payload)
	{
		$appId = $payload['application_id'] ?? null;
		if (! $appId) {
			Log::info('ReclamoService sin application_id', ['datar' => $payload]);
			return;
		}
		$this->forClient($appId);
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;
		$coleccion = new Collection($payload['actions']);
		if (!$resource || !$userId) return;
		if ($coleccion->contains('claims')) {
			$aact = $coleccion->first(function ($value) {
				return $value === 'claims';
			});
		}
		if ($coleccion->contains('claims_actions')) {
			$aact = $coleccion->first(function ($value) {
				return $value === 'claims_actions';
			});
		}

		switch ($aact) {
			case 'claims':
				$response = $this->mlForClient()->apiGetDos($resource, $userId);

				if ($response['success']) {

					$order = $this->updateOrCreate($response['body'], $this->clienteId());
					Log::info("reclamo  registrada para CLIENTE {$this->clienteId()}", [
						'claims_id' => $response['body']['id']
					]);
				}
				$this->ml->actualizar($resource);
				break;
			case 'claims_actions':
				$racciones = $this->mlForClient()->apiGetDos($resource, $userId);

				if ($racciones['success']) {
					$returnValue = explode('/', $resource);

					$order = $this->updateOrCreateAcciones($returnValue[4], $racciones['body'], $this->clienteId());
					Log::info("claims_actions  registrada para CLIENTE {$this->clienteId()}", [
						'claims_id' => $returnValue
					]);
				}
				$this->ml->actualizar($resource);

				break;
		}
	}

	public function getSinLeerLocal()
	{
		$datos = [];
		$clientes = MLApp::with('usuario')->whereHas('usuario')->get();

		foreach ($clientes as $cliente) {

			$cantidadPendientes = MLReclamo::where('meli_user_id', $cliente->app_id)
				->where('status', 'opened')
				->get()
				->filter(function ($reclamo) {
					return $this->waitingForByMessages($reclamo) === 'seller';
				})
				->count();

			$datos[] = [
				'client_id' => $cliente->app_id,
				'cantidad' => $cantidadPendientes
			];
		}

		return $datos;
	}

	public function mensajes($reclamoId, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;
		$mensaje = $this->mlForClient()->apiGetDos('/post-purchase/v1/claims/' . $reclamoId . '/messages', $meli_user_id);
		if ($mensaje['success']) {
			foreach ($mensaje['body'] as $msg) {
				$exi = MLReclamoMensaje::where('hash', $msg['hash'])->first();
				if ($exi !== null) continue;
				MLReclamoMensaje::updateOrCreate(
					['hash' => $msg['hash']], // 👈 CLAVE REAL
					[
						'reclamo_id'   => $reclamoId,
						'sender_role'  => $msg['sender_role'],
						'receiver_role' => $msg['receiver_role'] ?? null,
						'text'      => $msg['message'] ?? null,
						'attachment_path'  => $msg['attachments'][0]['filename']
							?? null,
						'date_created' => $msg['date_created'],
						'payload' => $msg,
						'date_read'    => isset($msg['date_read'])
							? $msg['date_read']
							: null,
						'translated'   => !empty($msg['translated_message']),
					]
				);
			}
		}
	}


	public function waitingFor($reclamo): ?string
	{
		$players = $reclamo->payload['players'] ?? [];

		foreach ($players as $player) {
			if (
				$player['type'] === 'seller' &&
				!empty($player['available_actions'])
			) {
				return 'seller';
			}

			if (
				$player['type'] === 'buyer' &&
				!empty($player['available_actions'])
			) {
				return 'buyer';
			}
		}

		return null;
	}



	public function waitingForByMessages($reclamo): ?string
	{
		$messages = $reclamo->mensajes ?? [];

		if (empty($messages)) {
			return null;
		}

		$last = collect($messages)->sortBy('date_created')->last();

		if (empty($last)) {
			return null;
		}
		if (
			$last['sender_role'] === 'respondent' &&
			$last['receiver_role'] === 'complainant'
		) {
			return 'buyer';
		}

		if (
			$last['sender_role'] === 'complainant' &&
			$last['receiver_role'] === 'respondent'
		) {
			return 'seller';
		}

		return null;
	}

	private function processClaimActions(array $claim): array
	{
		$actions = [];
		$deadline = null;
		$hasMandatoryAction = false;

		foreach ($claim['players'] as $player) {
			if ($player['role'] === 'respondent' && $player['type'] === 'seller') {
				foreach ($player['available_actions'] as $actionData) {
					$action = $actionData['action'];

					// Guardar deadline si existe
					if ($action === 'send_message_to_complainant' && $actionData['due_date']) {
						$deadline = $actionData['due_date'];
						$hasMandatoryAction = $actionData['mandatory'];
					}

					// Filtrar acciones para mostrar (como en la imagen)
					if (in_array($action, ['allow_partial_refund', 'refund', 'open_dispute'])) {
						$actions[] = [
							'action' => $action,
							'label' => $this->getActionLabel($action),
							'due_date' => $actionData['due_date'],
							'mandatory' => $actionData['mandatory'],
						];
					}
					$this->sortActions($actions);
				}
				break;
			}
		}

		return [
			'display_actions' => $actions, // Las 3 que se muestran
			'deadline' => $deadline,
			'has_mandatory_action' => $hasMandatoryAction,
			'mandatory_action' => 'send_message_to_complainant',
		];
	}

	private function sortActions(array &$actions): void
	{
		$order = [
			'allow_partial_refund' => 1,
			'refund' => 2,
			'open_dispute' => 3,
			'send_message_to_complainant' => 4,
			'allow_return' => 5
		];

		usort($actions, function ($a, $b) use ($order) {
			return ($order[$a['action']] ?? 999) <=> ($order[$b['action']] ?? 999);
		});
	}

	private function getActionLabel(string $action): string
	{
		$labels = [
			'allow_partial_refund' => 'Elegir monto a reembolsar',
			'refund' => 'Ofrecer devolución',
			'open_dispute' => 'Contactar a Mercado Libre',
			'send_message_to_complainant' => 'Responder al comprador',
			'allow_return' => 'Permitir devolución',
		];


		return $labels[$action] ?? $action;
	}

	public function mensajesDetalleMejorado($reclamoId)
	{
		$reclamo = MLReclamo::where('reclamo_id', $reclamoId)
			->first();
		$detalle = MLOrden::where('orden_id', $reclamo->resource_id)
			->orWhere('envio_id', $reclamo->resource_id)->first();
		// 1) Normalizar payload de la venta
		$venta_payload = is_string($detalle['payload'])
			? json_decode($detalle['payload'], true)
			: $detalle['payload'];
		$reclamo_payload = is_string($reclamo['payload'])
			? json_decode($reclamo['payload'], true)
			: $reclamo['payload'];
		// 2) Productos (simple, corto y optimizado)
		$productos = collect($venta_payload['order_items'] ?? [])
			->map(fn($i) => [
				"producto" => $this->itemService->detalle($i['item']['id']),
				"cantidad" => $i['quantity'] ?? '',
				"seller_sku" => $i['item']['seller_sku'] ?? '',
				"precio"   => number_format($i['full_unit_price'], 2, ',', '.') ?? '',
				"color" =>	collect($i['item']['variation_attributes'])
					->firstWhere('id', 'COLOR')['value_name'] ?? null
			])
			->values()
			->toArray();

		// 3) Mensajes agrupados por fecha + normalizados
		$mensajes = MLReclamoMensaje::where('reclamo_id', $reclamoId)
			->orderBy('date_created')
			->select('date_created', 'payload', 'sender_role',  'attachment_path')
			->get()
			->map(function ($m) {

				// Decodificar una sola vez
				$payload = is_string($m->payload)
					? json_decode($m->payload, true)
					: $m->payload;

				return [
					//"fecha"          => $m->date_created->format("Y-m-d H:i:s"),
					"fecha"          => \Carbon\Carbon::parse($m->date_created)->setTimezone(config('app.timezone'))->format("Y-m-d H:i:s") ?? '',
					"attachment_path" => $m->attachment_path ?? null,
					"sender_role" => $m->sender_role ?? null,
					"raw"            => $payload
				];
			})
			->groupBy(
				fn($m) =>
				\Carbon\Carbon::parse($m["fecha"])->format("d/m/Y")
			)
			->toArray();
		$detReclamo = $this->processClaimActions($reclamo['payload']);
		// 4) Estructura final
		return [
			"id"                    => $reclamoId,
			"orden_id" => $detalle['orden_id'],
			"date_created" => \Carbon\Carbon::parse($reclamo_payload['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
			"estado"                => $reclamo->status ?? '',
			"comprador"             => [
				"id"   => $venta_payload['buyer']['id'] ?? '',
				"nickname"   => $venta_payload['buyer']['nickname'] ?? '',
				"first_name" => $venta_payload['buyer']['first_name'] ?? '',
				"last_name"  => $venta_payload['buyer']['last_name'] ?? '',
				"seller"  => $venta_payload['seller']['id'] ?? ''
			],

			/*'motivo' => MercadoLibreClaimHelper::buildReason(
				$reclamo->motivos['name'] ?? '',
				$reclamo->motivos['detail']
			),*/
			'motivo'=>$reclamo['detalle']??[],
			"compra"   => $productos,
			'displayActions' => $detReclamo['display_actions'],
			'deadline' => $detReclamo['deadline'],
			'hasMandatoryAction' => $detReclamo['has_mandatory_action'],
			'mandatoryAction' => $detReclamo['mandatory_action'],
			"mensajes" => $mensajes
		];
	}

	public function uploadAttachments($clientId, int $claimId, array $files): array
	{
		$cliente = MLApp::with('usuario')
			->where('app_id', $clientId)
			->first();
		$ml = app(MercadoLibreService::class)->forClient($clientId);
		$token = $ml->getAccessToken($cliente->usuario->meli_user_id);

		$attachments = [];

		foreach ($files as $file) {
			$response = Http::withToken($token)
				->attach(
					'file',
					file_get_contents($file->getRealPath()),
					$file->getClientOriginalName()
				)
				->post("https://api.mercadolibre.com/post-purchase/v1/claims/{$claimId}/attachments");

			if ($response->failed()) {
				Log::error('Error subiendo adjunto ML', [
					'claim_id' => $claimId,
					'error' => $response->body()
				]);

				throw new \Exception('Error al subir adjunto a Mercado Libre');
			}
			$attachments[] = [
				'id' => $response->json('file_name'),
				'user_id' => $response->json('user_id'),
				//'file_name' => $file->getClientOriginalName()
			];
		}

		return $attachments;
	}

	public function sendMessageWithAttachments(
		$clientId,
		int $claimId,
		?string $message = null,
		array $attachmentIds = []
	) {
		$cliente = MLApp::with('usuario')
			->where('app_id', $clientId)
			->firstOrFail();

		$ml = app(MercadoLibreService::class)->forClient($clientId);
		$token = $ml->getAccessToken($cliente->usuario->meli_user_id);

		// Base obligatoria
		$payload = [
			'receiver_role' => 'complainant',
			'message' => $message,
		];

		// ⚠️ REGLA ML: texto y adjuntos NO van juntos
		if (!empty($attachmentIds)) {
			$payload['attachments'] = collect($attachmentIds)
				->map(fn($id) => $id)
				->values()
				->toArray();
		} elseif (!empty($message)) {
			$payload['message'] = $message;
		} else {
			throw new \InvalidArgumentException('Debe enviar mensaje o adjuntos');
		}

		$response = Http::withToken($token)
			->post(
				"https://api.mercadolibre.com/post-purchase/v1/claims/{$claimId}/actions/send-message",
				$payload
			);

		if ($response->failed()) {
			Log::error('Error enviando mensaje reclamo ML', [
				'claim_id' => $claimId,
				'payload'  => $payload,
				'error'    => $response->body(),
			]);

			throw new \Exception($response->json('message') ?? 'Error al enviar mensaje del reclamo');
		}

		return $response->json();
	}
}
