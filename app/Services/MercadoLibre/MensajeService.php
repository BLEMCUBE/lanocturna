<?php

namespace App\Services\MercadoLibre;

use App\Models\MLMensaje;
use App\Models\MLOrden;
use App\Models\MLCLient;
use App\Models\MLApp;
use App\Jobs\DetalleOrdenJob;
use App\Traits\BaseMLService;
use Illuminate\Support\Facades\Log;
use Exception;

class MensajeService
{
	use BaseMLService;

	protected $ml;
	protected $itemService;
	protected $usuarioService;
	//protected $clienteActual = null;

	public function __construct(
		MercadoLibreService $ml,
		ItemService $itemService,
		UsuarioService $usuarioService,
	) {
		$this->ml = $ml;
		$this->itemService = $itemService;
		$this->usuarioService = $usuarioService;
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
		$parametros = [
			'tag' => 'post_sale',
			'mark_as_read' => false,
		];

		$question = $this->mlForClient()->apiGet('/messages/' . $resource, $userId, $parametros);

		//crear
		$order = $this->saveMensajes($question);
		if ($order !== null) {
			Log::info("Mensaje registrada Notificacion [{$resource}]");
		}
		$this->ml->actualizar($resource);

		//notificion
		//$this->ml->pusherNotificacion('ml', 'question');
	}

	/**
	 * Obtener mensajes sin leer del cliente
	 */
	public function getSinLeer($clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return [];

		// Solo marca como leídos los del cliente actual
		MLMensaje::where('client_id', $this->clienteId())
			->where('is_read', 0)
			->update(['is_read' => 1]);

		$parametros = [
			'role' => 'seller',
			'tag'  => 'post_sale'
		];

		$response = $this->mlForClient()->apiGet('/messages/unread', $this->usuarioMeliId(), $parametros);
		return $response['results'] ?? [];
	}



	/**
	 * Obtener un pack completo
	 */
	public function getPack($packId, $clientId)
	{
		$offset = 0;
		$limit = 50;

		$parametros = [
			'tag' => 'post_sale',
			'mark_as_read' => false,
			'offset' => $offset,
			'limit' => $limit,
		];
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return [];
		do {
			$response = $this->mlForClient()->apiGet("/messages" . $packId, $meli_user_id, $parametros);
			$messages = $response['messages'] ?? [];

			foreach ($messages as $msg) {
				$created = $msg['message_date']['created'] ?? null;
				$read = $msg['message_date']['read'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($meli_user_id);
				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;

				$seller_id = collect($msg['message_resources'])
					->firstWhere('name', 'sellers')['id'] ?? null;


				$orden = MLOrden::where('pack_id', $pack_id)
					->orWhere('orden_id', $pack_id)->first();
				if (is_null($orden)) {
					$response = $this->mlForClient()->apiGetDos('/packs/' . $pack_id, $meli_user_id);
					if ($response['success']) {
						foreach ($response['body']['orders'] as  $value) {
							DetalleOrdenJob::dispatch($value['id'], $this->clienteId(), $meli_user_id)->onQueue('meli');
						}
					} else {
						DetalleOrdenJob::dispatch($pack_id, $this->clienteId(), $meli_user_id)->onQueue('meli');
					}
				}
				MLMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
						'date_created' => $created,
						'text' => strval($msg['text']),
						'attachment_path' => $msg['message_attachments'][0]['filename']
							?? null,
						// si read ≠ null → lo leyó alguien → marcar como leído
						'is_read' => $read ? 1 : 0,
						// marcar si lo envió el vendedor
						'is_from_seller' => $fromSeller ? 1 : 0,
						'client_id' => $this->clienteId(),
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

	/**
	 * Guardar mensajes en DB siempre con app_id
	 */
	public function saveMensajes($question)
	{
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;
		if ($question['messages'] !== null) {

			foreach ($question['messages'] as  $msg) {
				$this->usuarioService->buscarUsuario($msg['from']['user_id'], $this->clienteId());
				$this->usuarioService->buscarUsuario($msg['to']['user_id'], $this->clienteId());
				$read = $msg['message_date']['read'] ?? null;
				$created = $msg['message_date']['created'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($meli_user_id);

				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;

				$seller_id = collect($msg['message_resources'])
					->firstWhere('name', 'sellers')['id'] ?? null;

				$orden = MLOrden::where('pack_id', $pack_id)
					->orWhere('orden_id', $pack_id)->first();

				if (is_null($orden)) {
					$response = $this->mlForClient()->apiGetDos('/packs/' . $pack_id, $meli_user_id);
					if ($response['success']) {
						foreach ($response['body']['orders'] as  $value) {
							DetalleOrdenJob::dispatch($value['id'], $this->clienteId(), $meli_user_id)->onQueue('meli');
						}
					} else {
						DetalleOrdenJob::dispatch($pack_id, $this->clienteId(), $meli_user_id)->onQueue('meli');
					}
				}
				$item = MLCLient::with('cliente')
					->where('meli_user_id', $seller_id)
					->first();

				$data =	MLMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
						'date_created' => $created,
						'text' => strval($msg['text']),
						'attachment_path' => $msg['message_attachments'][0]['filename']
							?? null,
						// si read ≠ null → lo leyó alguien → marcar como leído
						'is_read' => $read ? 1 : 0,
						// marcar si lo envió el vendedor
						'is_from_seller' => $fromSeller ? 1 : 0,
						'client_id' => $this->clienteId(),
						// guardar JSON entero
						'payload' => $msg,


					]
				);
			}
		}
		return true;
	}

	public function getSinLeerLocal()
	{
		$datos = [];
		$clientes = MLApp::with('usuario')->whereHas('usuario')->get();

		foreach ($clientes as $key => $value) {
			$query = MLMensaje::select('id', 'pack_id', 'is_read', 'is_from_seller', 'client_id')
				->where('client_id', '=', $value['app_id'])
				->where('is_from_seller', '=', 0)
				->where('is_read', '=', 0)->count();

			array_push($datos, [
				'client_id' => $value['app_id'],
				'cantidad' => $query,
			]);
		}
		return $datos;
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
		$mensajes = MLMensaje::where('pack_id', $ventaId)
			->orderBy('date_created')
			->select('date_created', 'payload', 'is_from_seller','attachment_path')
			->get()
			->map(function ($m) {

				// Decodificar una sola vez
				$payload = is_string($m->payload)
					? json_decode($m->payload, true)
					: $m->payload;

				return [
					"fecha"          => $m->date_created->format("Y-m-d H:i:s"),
					"attachment_path"=> $m->attachment_path ?? null,
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
			"orden_id" => $detalle['orden_id'],
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
