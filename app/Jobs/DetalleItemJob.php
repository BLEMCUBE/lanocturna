<?php

namespace App\Jobs;

use App\Models\MLApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Services\MercadoLibre\ItemService;
use App\Models\MLItem;

class DetalleItemJob implements ShouldQueue
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

		$cliente = MLApp::with('usuario')
			->whereHas('usuario')
			->where('app_id', $this->clientId)
			->first();
		if (!is_null($cliente)) {

			$row = MLItem::where('item_id', '=',  $this->payload)->first();
			if (is_null($row)) {
				$ml = app(MercadoLibreService::class)->forClient($this->clientId);
				$item = $ml->apiGet('/items/' . $this->payload, $cliente->usuario->meli_user_id, []);

				$newItem = app(ItemService::class)->updateOrCreate($item);
			}
		}
	}
}
