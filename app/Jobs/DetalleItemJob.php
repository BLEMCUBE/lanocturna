<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Services\MercadoLibre\ItemService;
use App\Models\MLItem;
use App\Models\MLCLient;
use Illuminate\Support\Facades\Log;

class DetalleItemJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $tries = 5; // cantidad de intentos antes de fallar

	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	protected $payload;
	protected $userId;

	public function __construct($payload, $userId)
	{
		$this->payload = $payload;
		$this->userId = $userId;
	}

	public function handle()
	{

		$user = MLCLient::with('cliente')
			->where('meli_user_id', $this->userId)
			->first();
		if (!$user) return;
		$row = MLItem::where('item_id', '=',  $this->payload)->first();
		if ($row === null) {
			$ml = app(MercadoLibreService::class)->forClient($user->cliente->app_id);
			$item = $ml->apiGet('/items/' . $this->payload, $this->userId, []);

			$newItem = app(ItemService::class)->updateOrCreate($item);

			if ($newItem !== null) {
				Log::info("Item Creado [{$item['id']}]");
			}
		}
	}
}
