<?php

namespace App\Jobs;

use App\Models\MLApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;



class RunFetchRespuestasForAllClientsJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 5; // cantidad de intentos antes de fallar
	//public $backoff = 30; // segundos entre intentos (Laravel 10+)
	public function backoff()
	{
		return [10, 30, 60, 120, 300];
	}

	public function handle()
	{
		$clientes = MLApp::with('usuario')
			->whereHas('usuario')
			->get();

		foreach ($clientes as $cli) {
			FetchRespuestasJob::dispatch(
				$cli->app_id,
				$cli->usuario->meli_user_id,
				null
			)->onQueue('meli');
		}
	}
}
