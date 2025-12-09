<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ConfiguracionService;

class WCActualizarStockJob implements ShouldQueue
{
   	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 5; // cantidad de intentos antes de fallar
	//public $backoff = 30; // segundos entre intentos (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	protected $sku;
	protected $stock;

	public function __construct($sku,$stock)
	{
		$this->sku = $sku;
		$this->stock = $stock;
	}

    public function handle()
    {
            $sku = $this->sku;
            $stock = $this->stock;
			$url_tienda = app(ConfiguracionService::class)->getOption('url-tienda');

            $url = $url_tienda . "/wp-json/wclanocturnauy/v1/actualizar_stock?sku={$sku}";

            // Llamada HTTP usando Laravel
            $response = Http::timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
                ->asForm()
                ->post($url, [
                    'stock' => $stock
                ]);

            if ($response->failed()) {
                Log::error("❌ Error actualizando SKU {$sku}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

            }

            Log::info("✔ Stock actualizado | SKU {$sku} | Stock {$stock}");

    }
}
