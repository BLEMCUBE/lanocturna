<?php

namespace App\Jobs;

use App\Services\MercadoLibre\MercadoLibreService;
use App\Models\MLOrden;
use Illuminate\Bus\Queueable;
use App\Services\MercadoLibre\UsuarioService;
use App\Services\MercadoLibre\OrdenService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DetalleOrdenJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $orderId;
	public $clientId;
	public $userId;

	// Reintentos
	public $tries = 5;

	// Backoff progresivo (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	public function __construct($orderId,$clientId,$userId)
	{
		$this->orderId = $orderId;
		$this->clientId = $clientId;
		$this->userId = $userId;
	}

	public function handle()
	{
		$orden2 = MLOrden::where('orden_id', $this->orderId)->first();
		if (is_null($orden2)) {


				$ml = app(MercadoLibreService::class)->forClient($this->clientId);

				// Llamada a la API
				$response = $ml->apiGetDos("/orders/{$this->orderId}", $this->userId);

				if (!$response['success']) {
					// Lanzamos excepción para forzar reintento
					throw new \Exception("Error ML ({$response['status_code']}): " . json_encode($response['body']));
				}

				$order = $response['body'];

				// Guardamos o actualizamos la venta
				app(OrdenService::class)->updateOrCreate($order,$this->clientId);

		}
	}

	public function failed(Throwable $exception)
	{
		//Log::critical("❌ Job falló definitivamente para la orden {$this->orderId}: {$exception->getMessage()}");
	}
}
