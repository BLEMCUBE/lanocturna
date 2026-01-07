<?php

namespace App\Jobs;

use App\Models\MLApp;
use App\Models\MLCampaniaItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoLibre\MercadoLibreService;
use Carbon\Carbon;
use App\Services\MercadoLibre\CampaniaService;

class PublicidadJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $tries = 5; // cantidad de intentos antes de fallar

	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	protected $payload;
	protected $clientId;

	public function __construct($payload, $clientId)
	{
		$this->payload = $payload;
		$this->clientId = $clientId;
	}

	public function handle()
	{

		$fFrom = Carbon::now()->subDay(30)->format('Y-m-d');
		$fTo = Carbon::now()->format('Y-m-d');
		$cliente = MLApp::with('usuario')
			->whereHas('usuario')
			->where('app_id', $this->clientId)
			->first();
		if (is_null($cliente)) return;

		$user_id = $cliente->usuario->meli_user_id;
		$ad_id = $cliente->usuario->ad_id;
		if (!is_null($cliente)) {
			$parametros2 = [
				"date_from" => $fFrom,
				"date_to" => $fTo,
				"filters[item_ids]" => implode(",", $this->payload['items']),
				"metrics" => "clicks,prints,direct_units_quantity,indirect_units_quantity,direct_amount,indirect_amount",
			];

			$ml = app(MercadoLibreService::class)->forClient($this->clientId);
			$responseI = $ml->apiGetDos(
				"/advertising/MLU/advertisers/" . $ad_id . "/product_ads/campaigns/" . $this->payload['campaign_id'] . "/ads/metrics",
				$user_id,
				$parametros2
			);

			$datos = [];
			if (! $responseI['success']) {
				return $datos;
			}

			foreach ($responseI['body'] as $ad1) {
				if (!isset($ad1['results']) || empty($ad1['results'])) {
					continue;
				}

				foreach ($ad1['results'] as $metric) {
					$existeI = MLCampaniaItem::where('item_id', $metric['item_id'])
						->where('fecha', $ad1['date'])
						->where('campaign_id', $this->payload['campaign_id'])
						->where('ad_group_id', $this->payload['ad_group_id'])
						->first();
					if (!$existeI) {
						$datoI = [
							"client_id" => $this->clientId,
							"campaign_id" => $this->payload['campaign_id'],
							"ad_group_id" => $this->payload['ad_group_id'] ?? null,
							"advertiser_id" => $this->payload['advertiser_id'] ?? null,
							"item_id" => $metric['item_id'] ?? null,
							"status" => $this->payload['status'] ?? null,
							"date" => $ad1['date'] ?? null,
							"sku" => app(CampaniaService::class)->extractSkuMLPayload($metric['item_id']),
							"clicks" => $metric['metrics']['clicks'] ?? 0,
							"prints" => $metric['metrics']['prints'] ?? 0,
							"direct_amount" => $metric['metrics']['direct_amount'] ?? 0,
							"indirect_amount" => $metric['metrics']['indirect_amount'] ?? 0,
							"direct_units_quantity" => $metric['metrics']['direct_units_quantity'] ?? 0,
							"indirect_units_quantity" => $metric['metrics']['indirect_units_quantity'] ?? 0
						];



						app(CampaniaService::class)->ItemUpdateOrCreate($datoI, $this->clientId);
					}
				}
			}
		}
	}
}
