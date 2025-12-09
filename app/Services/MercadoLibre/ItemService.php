<?php

namespace App\Services\MercadoLibre;

use App\Models\MLItem;
use Illuminate\Support\Facades\Log;
use App\Helpers\HelperMercadoLibre;
use App\Traits\BaseMLService;

class ItemService
{
	use BaseMLService;
	public function __construct(
		private	MercadoLibreService $ml,
	) {}

	public function updateOrCreate($item)
	{

		/*
		$row = MLItem::where('item_id', '=', $item['id'])->first();
		if ($row === null) {
			*/
		$data =	MLItem::updateOrCreate(
			['item_id' => $item['id']],
			[
				'title' => $item['title'] ?? null,
				'category_id' => $item['category_id'] ?? null,
				'seller_id' => $item['seller_id'] ?? null,
				'status' => $item['status'] ?? null,
				'payload' => $item,
			]
		);
		return $data;
		/*
		}
		return null;
		*/
	}



	//crear desde notificacion
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

		$item = $this->mlForClient()->apiGet($resource, $userId, []);

		$newItem = $this->updateOrCreate($item);

		if ($newItem !== null) {
			Log::info("Item Creado [{$item['id']}]");
		}
		$this->ml->actualizar($resource);
	}

	public function detalle($item_id, $lista = false)
	{

		if ($lista == false) {
			$query_item = MLItem::where('item_id', $item_id)->first();
			$item = $query_item->payload;
			$sellerSku = collect($item['attributes'])
				->firstWhere('id', 'SELLER_SKU')['value_name'] ?? null;
			return [
				'title' => $item['title'],
				'id' => $item['id'],
				'thumbnail' => $item['thumbnail'],
				'sku' => $sellerSku,
				'permalink' => $item['permalink'],
				'base_price' => number_format($item['base_price'], 2, ',', '.') ?? 0,
				'listing_type_id' => HelperMercadoLibre::tipo($item['listing_type_id']),

			];
		} else {

			$query_item = MLItem::whereIn('item_id', $item_id)->get();

			if (!is_null($query_item)) {
				$datos = [];
				foreach ($query_item as $key => $value) {
					$item = $value->payload;
					$sellerSku = collect($item['attributes'])
						->firstWhere('id', 'SELLER_SKU')['value_name'] ?? null;
					array_push($datos, [
						'title' => $item['title'],
						'id' => $item['id'],
						'thumbnail' => $item['thumbnail'],
						'sku' => $sellerSku,
						'permalink' => $item['permalink'],
						'base_price' => number_format($item['base_price'], 2, ',', '.') ?? 0,
						'listing_type_id' => HelperMercadoLibre::tipo($item['listing_type_id']),

					]);
				}
				return $datos;
			} else {
				return [];
			}
		}
	}
}
