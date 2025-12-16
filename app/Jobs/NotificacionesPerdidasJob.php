<?php

namespace App\Jobs;

use App\Models\MLApp;
use App\Services\MercadoLibre\MissedFeedService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class NotificacionesPerdidasJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}


	public function handle()
	{
		$clientes=MLApp::with('usuario')->get();


		foreach ($clientes as $cliente) {
            $service = app(MissedFeedService::class)->forClient($cliente->app_id);

            $service->syncAllTopics($cliente->app_id);
        }
	}
}
