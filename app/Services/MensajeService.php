<?php

namespace App\Services;

use App\Jobs\FetchMercadoLibreOrder;
use App\Models\MercadoLibreMensaje;
use App\Models\MercadoLibreVenta;
use App\Services\MLUsuarioService;
use App\Services\ItemService;
use App\Services\MercadoLibreService;
use Illuminate\Support\Facades\Log;

class MensajeService
{
	public function __construct(
		private	MercadoLibreService $ml,
		private MLUsuarioService $mLUsuarioService,
		private ItemService $itemService
	) {}

	public function updateOrCreate($question)
	{
		$user = $this->mLUsuarioService->datosUsuario();
		if (!$user) return;
		if ($question['messages'] !== null) {

			foreach ($question['messages'] as  $msg) {
				$this->mLUsuarioService->buscarUsuario($msg['from']['user_id']);
				$this->mLUsuarioService->buscarUsuario($msg['to']['user_id']);
				$read = $msg['message_date']['read'] ?? null;
				$created = $msg['message_date']['created'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($user->meli_user_id);
				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;

				$orden = MercadoLibreVenta::where('pack_id', $pack_id)
					->orWhere('mercadolibre_venta_id', $pack_id)->first();
				if (is_null($orden)) {
					$response = $this->ml->apiGetDos('/packs/' . $pack_id, $user->meli_user_id);
					if ($response['success']) {
						foreach ($response['body']['orders'] as  $value) {
							FetchMercadoLibreOrder::dispatch($value['id'])->onQueue('meli');
						}
					} else {
						FetchMercadoLibreOrder::dispatch($pack_id)->onQueue('meli');
					}
				}

				$data =	MercadoLibreMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
						'date_created' => $created,
						'body' => strval($msg['text']),
						'attachment_path' => $msg['message_attachments'][0]['filename']
							?? null,
						// si read ≠ null → lo leyó alguien → marcar como leído
						'is_read' => $read ? 1 : 0,
						// marcar si lo envió el vendedor
						'is_from_seller' => $fromSeller ? 1 : 0,
						// guardar JSON entero
						'payload' => $msg,


					]
				);
			}
		}
		return true;
	}

	public function storeNotificacion($payload)
	{
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;
		if (!$resource || !$userId) return;
		$parametros = [
			'tag' => 'post_sale',
			'mark_as_read' => false,
		];
		$question = $this->ml->apiGet('/messages/' . $resource, $userId, $parametros);

		//crear
		$order = $this->updateOrCreate($question);
		if ($order !== null) {
			Log::info("Mensaje registrada Notificacion [{$resource}]");
		}
		$this->ml->actualizar($resource);
		//notificion
		$this->ml->pusherNotificacion('ml', 'question');
	}

	public function getSinLeer()
	{
		$user = $this->mLUsuarioService->datosUsuario();
		if (!$user) return;
		MercadoLibreMensaje::where('is_read', '=', 0)
			->update(['is_read' => 1]);
		$parametros = [
			'role' => 'seller',
			'tag' => 'post_sale'
		];

		$response = $this->ml->apiGet('/messages/unread', $user->meli_user_id, $parametros);
		return $response['results'] ?? [];
	}


	public function getPack($packId)
	{
		$offset = 0;
		$limit = 50;

		$parametros = [
			'tag' => 'post_sale',
			'mark_as_read' => false,
			'offset' => $offset,
			'limit' => $limit,
		];
		$user = $this->mLUsuarioService->datosUsuario();
		if (!$user) return;
		do {
			$response = $this->ml->apiGet("/messages" . $packId, $user->meli_user_id, $parametros);
			$messages = $response['messages'] ?? [];

			foreach ($messages as $msg) {
				$created = $msg['message_date']['created'] ?? null;
				$read = $msg['message_date']['read'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($user->meli_user_id);
				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;


				$orden = MercadoLibreVenta::where('pack_id', $pack_id)
					->orWhere('mercadolibre_venta_id', $pack_id)->first();
				if (is_null($orden)) {
					$response = $this->ml->apiGetDos('/packs/' . $pack_id, $user->meli_user_id);
					if ($response['success']) {
						foreach ($response['body']['orders'] as  $value) {

							FetchMercadoLibreOrder::dispatch($value['id'])->onQueue('meli');
						}
					} else {
						FetchMercadoLibreOrder::dispatch($pack_id)->onQueue('meli');
					}
				}
				MercadoLibreMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
						'date_created' => $created,
						'body' => strval($msg['text']),
						'attachment_path' => $msg['message_attachments'][0]['filename']
							?? null,
						// si read ≠ null → lo leyó alguien → marcar como leído
						'is_read' => $read ? 1 : 0,
						// marcar si lo envió el vendedor
						'is_from_seller' => $fromSeller ? 1 : 0,
						// guardar JSON entero
						'payload' => $msg,


					]
				);
			}

			// Paginación
			$offset += $limit;
			$total = $response['paging']['total'] ?? $offset;
		} while ($offset < $total);
	}

	public function marcarLeido()
	{
		$user = $this->mLUsuarioService->datosUsuario();
		if (!$user) return;
		$mensajes = MercadoLibreMensaje::where('is_read', '=', 0)->get();

		$parametros = [
			'tag' => 'post_sale',
			'mark_as_read' => false,
		];
		if (!empty($mensajes)) {
			foreach ($mensajes as $key => $value) {
				$question = $this->ml->apiGet('/messages/' . $value->message_id, $user->meli_user_id, $parametros);
				$this->updateOrCreate($question);
			}
		}
	}

	public function noLeidos()
	{
		$user = $this->mLUsuarioService->datosUsuario();
		if (!$user) return;
		$parametros = [
			'tag' => 'post_sale',
			'role' => 'seller',
		];
		return $this->ml->apiGet('/messages/unread', $user->meli_user_id, $parametros);
	}

	public function noLeidosLocal()
	{
		$total = MercadoLibreMensaje::select('id')->where('is_read', '=', 0)->count();
		return $total;
	}

	public function mensajesDetalleMejorado($ventaId, $detalle)
	{
		// 1) Normalizar payload de la venta
		$venta_payload = is_string($detalle['payload'])
			? json_decode($detalle['payload'], true)
			: $detalle['payload'];

		// 2) Productos (simple, corto y optimizado)
		$productos = collect($venta_payload['order_items'] ?? [])
			->map(fn($i) => [
				//"producto" => $i['item']['title'] ?? '',
				"producto" => $this->itemService->detalle($i['item']['id']),
				"cantidad" => $i['quantity'] ?? '',
				"seller_sku" => $i['item']['seller_sku'] ?? '',
				"precio"   => number_format($i['full_unit_price'],2,',','.') ?? '',
				"color" =>	collect($i['item']['variation_attributes'])
					->firstWhere('id', 'COLOR')['value_name'] ?? null
			])
			->values()
			->toArray();

		// 3) Mensajes agrupados por fecha + normalizados
		$mensajes = MercadoLibreMensaje::where('pack_id', $ventaId)
			->orderBy('date_created')
			->select('date_created', 'payload', 'is_from_seller')
			->get()
			->map(function ($m) {

				// Decodificar una sola vez
				$payload = is_string($m->payload)
					? json_decode($m->payload, true)
					: $m->payload;

				return [
					"fecha"          => $m->date_created->format("Y-m-d H:i:s"),
					"attachment_path"          => $m->attachment_path ?? null,
					//"from_id"        => $payload["from"]["user_id"] ?? null,
					"is_from_seller" => $m->is_from_seller,
					"raw"            => $payload
				];
			})
			->groupBy(
				fn($m) =>
				\Carbon\Carbon::parse($m["fecha"])->format("d/m/Y")
			)
			->toArray();

		// 4) Estructura final
		return [
			"id"                    => $ventaId,
			"pack_id"               => $detalle['pack_id'],
			"mercadolibre_venta_id" => $detalle['mercadolibre_venta_id'],
			"date_created" => \Carbon\Carbon::parse($venta_payload['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
			"estado"                => $venta_payload['fulfilled'] ?? '',
			"comprador"             => [
				"id"   => $venta_payload['buyer']['id'] ?? '',
				"nickname"   => $venta_payload['buyer']['nickname'] ?? '',
				"first_name" => $venta_payload['buyer']['first_name'] ?? '',
				"last_name"  => $venta_payload['buyer']['last_name'] ?? '',
				"seller"  => $venta_payload['seller']['id'] ?? ''
			],
			"compra"   => $productos,
			"mensajes" => $mensajes
		];
	}
}
