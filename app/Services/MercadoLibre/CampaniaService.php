<?php

namespace App\Services\MercadoLibre;

use App\Models\MLApp;
use App\Models\MLCampania;
use App\Models\MLCampaniaItem;
use App\Models\MLOrden;
use App\Traits\BaseMLService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaniaService
{
	use BaseMLService;

	public function __construct(
		private MercadoLibreService $ml
	) {}


	/**
	 * Crear o actualizar reclamo
	 */
	public function CampaniaUpdateOrCreate($item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;

		// Guardar reclamo
		$camp = MLCampania::updateOrCreate(
			['campaign_id' => $item['id']],
			[
				'client_id'   => $this->clienteId(),
				'campaign_id' => $item['id'],
				'name'     => $item['name'] ?? null,
				'status'     => $item['status'] ?? null,
				'date_created'     =>  $item['date_created'] ?? null,
				'last_updated'     =>  $item['last_updated'] ?? null,
				'strategy'     => $item['strategy'] ?? null,
				'channel'   => $item['channel'] ?? null,
				'metrics'     => $item,
			]
		);
	}

	public function ItemUpdateOrCreate($item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;

		// Guardar
		$item = MLCampaniaItem::updateOrCreate(
			['item_id' => $item['item_id'], 'fecha' => $item['date'], 'campaign_id' => $item['campaign_id'], 'ad_group_id' => $item['ad_group_id']],
			[
				'client_id'   => $this->clienteId(),
				'campaign_id' => $item['campaign_id'] ?? null,
				'ad_group_id' => $item['ad_group_id'] ?? null,
				'advertiser_id' => $item['advertiser_id'] ?? null,
				'item_id' => $item['item_id'] ?? null,
				'status' => $item['status'] ?? null,
				'fecha' => $item['date'] ?? null,
				'sku' => $item['sku'] ?? null,
				'clicks' => $item['clicks'] ?? 0,
				'prints' => $item['prints'] ?? 0,
				'direct_amount' => $item['direct_amount'] ?? 0,
				'indirect_amount' => $item['indirect_amount'] ?? 0,
				'direct_units_quantity' => $item['direct_units_quantity'] ?? 0,
				'indirect_units_quantity' => $item['indirect_units_quantity'] ?? 0
			]
		);
	}

	public function extractSkuMLPayload($itemId)
	{
		// Buscar en la base de datos
		$item = DB::table('ml_items')
			->whereRaw("payload->>'$.id' = ?", [$itemId])
			->first();

		if (!$item) {
			$ord = MLOrden::where('item_id', $itemId)
				->select('item_id', 'item_sku')->first();
			return $ord ? $ord->item_sku : null;
		}

		// Decodificar JSON
		$payload = json_decode($item->payload, true);

		// Buscar SKU en attributes (formato de tu ejemplo)
		if (isset($payload['attributes']) && is_array($payload['attributes'])) {
			foreach ($payload['attributes'] as $attribute) {
				// Verificar si es el atributo SKU
				if (isset($attribute['id']) && $attribute['id'] === 'SELLER_SKU') {
					// Tu ejemplo tiene el SKU en value_name
					if (isset($attribute['value_name']) && !empty($attribute['value_name'])) {
						return $attribute['value_name']; // "MAR-3148"
					}

					// Algunos items podrían tenerlo en values[0].name
					if (isset($attribute['values'][0]['name']) && !empty($attribute['values'][0]['name'])) {
						return $attribute['values'][0]['name'];
					}
				}
			}
		}

		return null;
	}
}
