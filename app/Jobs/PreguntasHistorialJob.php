<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\MercadoLibre\PreguntaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Throwable;

class PreguntasHistorialJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $itemId;
	public $clientId;

	// Reintentos
	public $tries = 5;

	// Backoff progresivo (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	public function __construct($itemId,$clientId)
	{
		$this->itemId = $itemId;
		$this->clientId = $clientId;
	}

	public function handle()
	{
			app(PreguntaService::class)->getPorItem($this->itemId,$this->clientId);

	}

	public function failed(Throwable $exception)
	{
		//Log::critical("❌ Job falló definitivamente para la orden {$this->orderId}: {$exception->getMessage()}");
	}
}
