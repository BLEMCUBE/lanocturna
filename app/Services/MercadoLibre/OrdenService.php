<?php

namespace App\Services\MercadoLibre;

use App\Models\MLApp;
use App\Models\MLOrden;
use App\Traits\BaseMLService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrdenService
{
	use BaseMLService;

	public function __construct(
		private MercadoLibreService $ml,
		private ItemService $itemService,
		private UsuarioService $mLUsuarioService
	) {}

	/**
	 * Crear o actualizar venta
	 */
	public function updateOrCreate($item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;
		// Registrar usuario comprador y vendedor si no existen
		$this->mLUsuarioService->buscarUsuario($item['buyer']['id'], $this->clienteId());
		$this->mLUsuarioService->buscarUsuario($item['seller']['id'], $this->clienteId());

		// Obtener lista de item_ids asociados a esta venta
		$items = collect($item['order_items'])
			->pluck('item.id')
			->filter()
			->values()
			->toArray();
		$item_sku = collect($item['order_items'])
			->pluck('item.seller_sku')
			->filter()
			->values()
			->toArray();

		// Guardar venta
		$venta = [];
		$row = MLOrden::where('orden_id', '=', $item['id'])->first();
		if ($row == null) {
			$venta = MLOrden::updateOrCreate(
				['orden_id' => $item['id']],
				[
					'client_id'   => $this->clienteId(),
					'orden_id' => $item['id'],
					'pack_id'     => $item['pack_id'] ?? null,
					'buyer_id'    => $item['buyer']['id'] ?? null,
					'seller_id'   => $item['seller']['id'] ?? null,
					'status'      => $item['status'] ?? 'pending',
					'date_created'     =>  $item['date_created'] ?? null,
					'last_updated'     =>  $item['last_updated'] ?? null,
					'payload'     => $item,
					'item_id'     => $items[0],
				'item_sku' => Str::upper($item_sku[0] ?? ''),
					'envio_id'    => $item['shipping']['id'] ?? null,
					//	'item_ids'    => $items,
				]
			);
			Log::info("Orden Creado [{$item['id']}]");
		} else {
			$fA = Carbon::parse($row['last_updated'])->format('Y-m-d H:i');
			$fI = Carbon::parse($item['last_updated'])->format('Y-m-d H:i');
			if ($fA !== $fI) {
				$venta = MLOrden::updateOrCreate(
					['orden_id' => $item['id']],
					[
						'client_id'   => $this->clienteId(),
						'orden_id' => $item['id'],
						'pack_id'     => $item['pack_id'] ?? null,
						'buyer_id'    => $item['buyer']['id'] ?? null,
						'seller_id'   => $item['seller']['id'] ?? null,
						'status'      => $item['status'] ?? 'pending',
						'date_created'     =>  $item['date_created'] ?? null,
						'last_updated'     =>  $item['last_updated'] ?? null,
						'payload'     => $item,
						'item_id'     => $items[0],
						'item_sku' => Str::upper($item_sku[0] ?? ''),
						'envio_id'    => $item['shipping']['id'] ?? null,
						//'item_ids'    => $items,
					]
				);
				Log::info("Orden Actualizado [{$item['id']}]");
			}
		}
		// Registrar detalles de los items
		foreach ($items as $itemId) {
			$itemData = $this->mlForClient()->apiGet('/items/' . $itemId, $meli_user_id);
			$this->itemService->updateOrCreate($itemData);
		}
		$this->agregarEnvio($item['id'], $clientId);
		$this->agregarCostoEnvio($item['id'], $clientId);

		return $venta;
	}


	/**
	 * Notificación desde Mercado Libre
	 */
	public function storeNotificacion($payload)
	{
		$appId = $payload['application_id'] ?? null;

		if (! $appId) {
			Log::warning('OrdenService sin application_id', $payload);
			return;
		}
		$this->forClient($appId);
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;
		$acciones = implode(',', $payload['actions']);

		$coleccion = new Collection($payload['actions']);
		if (!$resource || !$userId) return;
		$aact = '';
		if ($coleccion->contains('status')) {
			$aact = $coleccion->first(function ($value) {
				return $value === 'status';
			});
		}
		if ($coleccion->contains('fulfilled')) {
			$aact = $coleccion->first(function ($value) {
				return $value === 'fulfilled';
			});
		}

		switch ($aact) {
			case 'status':
				$response = $this->mlForClient()->apiGetDos($resource, $userId);

				if ($response['success']) {
					$returnValue = explode('/', $resource);
					$exist = MLOrden::where('orden_id', '=', $returnValue[2])
						->where('status', $response['body']['status'])
						->first();
					if (is_null($exist)) {

						$order = $this->updateOrCreate($response['body'], $this->clienteId());
						Log::info("Orden actualizada status para CLIENTE {$this->clienteId()}", [
							'orden_id' => $response['body']['id']
						]);
					}
					$this->ml->actualizar($resource, $acciones);
				}

				break;
			case 'fulfilled':
				$response = $this->mlForClient()->apiGetDos($resource, $userId);

				if ($response['success']) {
					$returnValue = explode('/', $resource);
					$exist = MLOrden::where('orden_id', '=', $returnValue[2])->first();
					if (is_null($exist)) {

						$order = $this->updateOrCreate($response['body'], $this->clienteId());
						Log::info("Orden actualizada fulfilled para CLIENTE {$this->clienteId()}", [
							'orden_id' => $response['body']['id']
						]);
					}
					$this->ml->actualizar($resource, $acciones);
				}

				break;

			default:
				$returnValue = explode('/', $resource);
				$exist = MLOrden::where('orden_id', '=', $returnValue[2])->first();

				if ($exist === null) {

					$response = $this->mlForClient()->apiGetDos($resource, $userId);

					if ($response['success']) {

						$order = $this->updateOrCreate($response['body'], $this->clienteId());
						Log::info("Orden registrada para CLIENTE {$this->clienteId()}", [
							'orden_id' => $response['body']['id']
						]);
					}
				}
				$this->ml->actualizar($resource, $acciones);
				break;
		}
	}


	/**
	 * Obtener comprador por ID de venta
	 */
	public function comprador($orderId)
	{
		$venta = MLOrden::where('id', $orderId)->first();
		if (is_null($venta->payload)) {
			return [
				"id"         => '',
				"nickname"   => '',
				"last_name"  => '',
				"first_name" => '',
			];
		} else {
			$item = $venta->payload;
			return [
				"id"         => $item['buyer']['id'],
				"nickname"   => $item['buyer']['nickname'] ?? '',
				"last_name"  => $item['buyer']['last_name'] ?? '',
				"first_name" => $item['buyer']['first_name'] ?? '',
			];
		}
	}

	/**
	 * Obtener comprador por ID de venta
	 */
	public function compradorPorOrdenId($orderId)
	{
		$venta = MLOrden::where('orden_id', $orderId)->first();

		if (is_null($venta)) {
			return [
				"id"         => '',
				"nickname"   => '',
				"last_name"  => '',
				"first_name" => '',
			];
		} else {
			$item = $venta->payload;
			return [
				"id"         => $item['buyer']['id'],
				"nickname"   => $item['buyer']['nickname'] ?? '',
				"last_name"  => $item['buyer']['last_name'] ?? '',
				"first_name" => $item['buyer']['first_name'] ?? '',
			];
		}
	}

	public function getSinLeerLocal()
	{

		$datos = [];
		$clientes = MLApp::with('usuario')->whereHas('usuario')->get();

		foreach ($clientes as $key => $value) {
			array_push($datos, [
				'client_id' => $value['app_id'],
				'cantidad' => 0,
			]);
		}
		return $datos;
	}


	public function agregarEnvio($ordenId, $client_id)
	{
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)
			->firstOrFail();

		$envio = MLOrden::where('orden_id', $ordenId)->firstOrFail();

		$shippingId = $envio->payload['shipping']['id'];
		if (!is_null($shippingId)) {

			$ml = app(MercadoLibreService::class)->forClient($client_id);
			$response = $ml->apiGetDos("/shipments/{$shippingId}", $cliente->usuario->meli_user_id);
			if (!$response['success']) {
				throw new \Exception("envio ({$response['status_code']}): " . json_encode($response['body']));
			}
			MLOrden::where('orden_id', $ordenId)->update([
				'envio_id' => $shippingId,
				'envio' => $response['body'],
			]);
			app(EnvioService::class)->forClient($client_id)->guardarActualizar($response['body'], $client_id);
		}
	}

	public function agregarFacturacion($ordenId, $client_id)
	{
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)
			->firstOrFail();

		$ml = app(MercadoLibreService::class)->forClient($client_id);

		$response = $ml->apiGetDos("/orders/{$ordenId}/billing_info", $cliente->usuario->meli_user_id);

		if (!$response['success']) {
			throw new \Exception("facturacion ({$response['status_code']}): " . json_encode($response['body']));
		}

		MLOrden::where('orden_id', $ordenId)->update([
			'facturacion' => $response['body']
		]);
	}

	public function agregarCostoEnvio($ordenId, $client_id)
	{
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)
			->firstOrFail();

		$envio = MLOrden::where('orden_id', $ordenId)->firstOrFail();

		$shippingId = $envio->payload['shipping']['id'];
		if (!is_null($shippingId)) {
			if (is_null($envio->costo_envio)) {
				$ml = app(MercadoLibreService::class)->forClient($client_id);
				$response = $ml->apiGetDos("/shipments/{$shippingId}/costs", $cliente->usuario->meli_user_id);
				if (!$response['success']) {
					throw new \Exception("costo_envio ({$response['status_code']}): " . json_encode($response['body']));
				}
				MLOrden::where('orden_id', $ordenId)->update([
					'envio_id' => $shippingId,
					'costo_envio' => $response['body']
				]);
				app(EnvioService::class)->forClient($client_id)->guardarCosto($shippingId, $response['body'], $client_id);
			}
		}
	}
}
