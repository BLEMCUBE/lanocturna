<?php

namespace App\Jobs;

use App\Services\ItemService;
use App\Services\MensajeService;
use App\Services\MLVentaService;
use App\Services\PreguntaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessMercadoLibreNotification implements ShouldQueue
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

	public function handle(): void
	{
		try {
			$topic = $this->payload['topic'];

			switch ($topic) {
				case 'items':
					app(ItemService::class)->storeNotificacion($this->payload);
					break;
				case 'questions':
					app(PreguntaService::class)->storeNotificacion($this->payload);
					break;
				case 'orders_v2':
					app(MLVentaService::class)->storeNotificacion($this->payload);
					break;
				case 'messages':
					app(MensajeService::class)->storeNotificacion($this->payload);
					break;
				default:
					Log::warning("Notificación ML ignorada, topic no manejado: {$topic}");
					break;
			}
		} catch (Throwable $e) {
			Log::error("Error procesando notificación ML: {$e->getMessage()}");

			// Reintentar después de 30 segundos
			throw $e;
		}
	}

	// Se ejecuta si después de $tries veces sigue fallando
	public function failed(Throwable $exception)
	{
		Log::critical("🔥 Job MercadoLibre falló definitivamente: " . $exception->getMessage(), [
			'payload' => $this->payload
		]);
	}
}
