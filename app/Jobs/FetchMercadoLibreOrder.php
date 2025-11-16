<?php

namespace App\Jobs;

use App\Services\MercadoLibreService;
use App\Models\MercadoLibreVenta;
use Illuminate\Bus\Queueable;
use App\Services\MLUsuarioService;
use App\Services\MLVentaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchMercadoLibreOrder implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $orderId;

	// Reintentos
	public $tries = 5;

	// Backoff progresivo (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	public function __construct($orderId)
	{
		$this->orderId = $orderId;
	}

	public function handle()
	{
		$orden2 = MercadoLibreVenta::where('mercadolibre_venta_id', $this->orderId)->first();
		if (is_null($orden2)) {
			$user = app(MLUsuarioService::class)->datosUsuario();
			if (!$user) return;
			try {

				/** @var MercadoLibreService $ml */
				$ml = app(MercadoLibreService::class);

				// Llamada a la API
				$response = $ml->apiGetDos("/orders/{$this->orderId}", $user->meli_user_id);

				if (!$response['success']) {
					// Lanzamos excepción para forzar reintento
					throw new \Exception("Error ML ({$response['status_code']}): " . json_encode($response['body']));
				}

				$order = $response['body'];

				// Guardamos o actualizamos la venta
				app(MLVentaService::class)->updateOrCreate($order);

			} catch (Throwable $e) {

				// Lanzamos excepción para activar reintentos automáticos
				throw $e;
			}
		}
	}

	public function failed(Throwable $exception)
	{
		//Log::critical("❌ Job falló definitivamente para la orden {$this->orderId}: {$exception->getMessage()}");
	}
}
