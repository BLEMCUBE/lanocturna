<?php

namespace App\Jobs;

use App\Models\Producto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ConfiguracionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncStockProductosTienda implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 5; // cantidad de intentos antes de fallar
	//public $backoff = 30; // segundos entre intentos (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	protected $payload;

	public function __construct($payload)
	{
		$this->payload = $payload;
	}
	public function handle()
	{
		$url_tienda = app(ConfiguracionService::class)->getOption('url-tienda');
		$url = $url_tienda . '/wp-json/wclanocturnauy/v1/get_skus';
		$response = Http::acceptJson()
			->get($url);

		if ($response->failed()) {
			Log::error('Error consultando Woo SKUs');
			return;
		}

		$data = $response->json();

		foreach ($data as $key => $value) {
			Producto::where('origen',$value['sku'])
			->where('is_store',0)
			->update(['is_store' => 1]);
		}

		Log::info('Sincronización Woo ejecutada correctamente', [
			'count' => count($data)
		]);
	}
}
