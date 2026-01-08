<?php

namespace App\Jobs;

use App\Models\MLApp;
use App\Models\MLCampania;
use App\Services\MercadoLibre\CampaniaService;
use App\Services\MercadoLibre\MercadoLibreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;



class RunFetchPublicidadForAllClientsJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 5; // cantidad de intentos antes de fallar
	//public $backoff = 30; // segundos entre intentos (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	public function handle()
	{

		$fFrom = Carbon::now()->subDays(30)->format('Y-m-d');
		$fTo = Carbon::now()->format('Y-m-d');

		$parametros = [
			"date_from" => $fFrom,
			"limit" => "800",
			"date_to" => $fTo,
			"filters[status]" => "active",
		];

		$clientes = MLApp::with('usuario:meli_user_id,app_id,ad_id')->whereHas('usuario')
			->select('id', 'nombre', 'app_id')->get();
		foreach ($clientes as $client) {

			$client_id = $client->app_id;
			$user_id = $client->usuario->meli_user_id;
			$ad_id = $client->usuario->ad_id;
			$ml = app(MercadoLibreService::class)->forClient($client_id);
			$response = $ml->apiGetDos(
				"/advertising/MLU/advertisers/" . $ad_id . "/product_ads/campaigns/search",
				$user_id,
				$parametros

			);

			$datos = [];
			if (! $response['success']) {
				return $datos;
			}


			foreach ($response['body']['results'] as $ad) {
				$existe = MLCampania::where('campaign_id', $ad['id'])->first();
				if (!$existe) {
					$dato = [
						"client_id" => $client_id,
						"id" => $ad['id'],
						"name" => $ad['name'] ?? null,
						"status" => $ad['status'] ?? null,
						"date_created" => $ad['date_created'] ?? null,
						"last_updated" => $ad['last_updated'] ?? null,
						"strategy" => $ad['strategy'] ?? null,
						"channel" => $ad['channel'] ?? null,
						"metrics" => $ad,
					];
					app(CampaniaService::class)->CampaniaUpdateOrCreate($dato, $client_id);
				}
			}

			$cont = $this->getAdItems($client, $fFrom, $fTo);
			$collection = collect($cont);
			$groupedData = $collection
				->groupBy(['campaign_id', 'ad_group_id']) // Agrupa por ambos niveles
				->map(function ($campaignGroup) {
					return $campaignGroup->map(function ($adGroup) {
						return [
							'campaign_id' => $adGroup->first()['campaign_id'],
							'ad_group_id' => $adGroup->first()['ad_group_id'],
							'items' => $adGroup->pluck('item_id')->toArray(),
							'advertiser_id' => $adGroup->first()['advertiser_id'],
							'status' => $adGroup->first()['status'],
							'item_count' => $adGroup->count()
						];
					});
				})
				->flatten(1)
				->values()
				->toArray();

			foreach ($groupedData as $key => $value) {
				PublicidadJob::dispatch(
					$value,
					$client_id
				)->onQueue('meli');
			}
		}
	}
	private function getAdItems($cliente, $fechaDesde = null, $fechaHasta = null)
	{
		$client_id = $cliente->app_id;
		$ml = app(MercadoLibreService::class)->forClient($client_id);
		$response = $ml->apiGetDos(
			"/advertising/MLU/advertisers/" . $cliente->usuario->ad_id . "/product_ads/ads/search",
			$cliente->usuario->meli_user_id,
			[
				"date_from" => $fechaDesde,
				"limit" => "800",
				"date_to" => $fechaHasta,
				"filters[statuses]" => "active",
			]
		);
		$datos = [];
		if (! $response['success']) {
			return $datos;
		}

		foreach ($response['body']['results'] as $ad) {
			$datos[] = [
				"item_id" => $ad['item_id'],
				"campaign_id" => $ad['campaign_id'],
				"ad_group_id" => $ad['ad_group_id'],
				"status" => $ad['status'],
				"advertiser_id" => $ad['advertiser_id'],

			];
		}
		return $datos;
	}

}
