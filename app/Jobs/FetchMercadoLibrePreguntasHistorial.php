<?php

namespace App\Jobs;

use App\Services\MercadoLibreService;
use App\Models\MercadoLibreVenta;
use Illuminate\Bus\Queueable;
use App\Services\MLUsuarioService;
use App\Services\MLVentaService;
use App\Services\PreguntaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchMercadoLibrePreguntasHistorial implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $itemId;

	// Reintentos
	public $tries = 5;

	// Backoff progresivo (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	public function __construct($itemId)
	{
		$this->itemId = $itemId;
	}

	public function handle()
	{
		$user = app(MLUsuarioService::class)->datosUsuario();
		if (!$user) return;
			app(PreguntaService::class)->getPorItem($this->itemId);

	}

	public function failed(Throwable $exception)
	{
		//Log::critical("❌ Job falló definitivamente para la orden {$this->orderId}: {$exception->getMessage()}");
	}
}
