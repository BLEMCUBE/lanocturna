<?php

namespace App\Services;

use App\Services\MercadoLibreService;
use App\Services\MLUsuarioService;
use App\Services\ItemService;
use App\Models\MercadoLibreVenta;
use Illuminate\Support\Facades\Log;

class MLVentaService
{
	public function __construct(
		private MercadoLibreService $ml,
		private ItemService $itemService,
		private MLUsuarioService $mLUsuarioService
	) {}

	public function updateOrCreate($item)
	{
		$user = $this->mLUsuarioService->datosUsuario();
		if (!$user) return;
		//existComprador
		$existComprador = $this->mLUsuarioService->buscarUsuario($item['buyer']['id']);
		$existVendedor = $this->mLUsuarioService->buscarUsuario($item['seller']['id']);

		//if ($row === null) {

		//ids items
		$items = collect($item['order_items'])
			->pluck('item.id')
			->filter()
			->values()
			->toArray();
		$data =	MercadoLibreVenta::updateOrCreate(
			['mercadolibre_venta_id' => $item['id']],
			[
				'mercadolibre_venta_id' => $item['id'] ?? null,
				'pack_id' => $item['pack_id'] ?? null,
				'claim_id' => $item['claim_id'] ?? null,
				'buyer_id' => $item['buyer']['id'] ?? null,
				'seller_id' => $item['seller']['id'] ?? null,
				'status' => $item['status'] ?? 'pending',
				'payload' => $item,
				'item_ids' => $items,
			]

		);

		foreach ($items as $key => $value) {
			$item = $this->ml->apiGet('/items/' . $value, $user->meli_user_id);
			$this->itemService->updateOrCreate($item);
		}
		return $data;
	}

	public function storeNotificacion($payload)
	{
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;
		if (!$resource || !$userId) return;

		$question = $this->ml->apiGetDos($resource, $userId);
		if ($question['success']) {
			//crear
			$order = $this->updateOrCreate($question['body']);
		}

		$this->ml->actualizar($resource);
	}

	public function comprador($orderId)
	{
		$query = MercadoLibreVenta::where('id', $orderId)->first();
		$item = $query->payload;
		if (!is_null($item)) {
			return [
				"id" => $item['buyer']['id'],
				"nickname" => $item['buyer']['nickname'],
				"last_name" => $item['buyer']['last_name'],
				"first_name" => $item['buyer']['first_name']
			];
		}
		return [];
	}
}
