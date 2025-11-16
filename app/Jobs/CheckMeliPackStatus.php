<?php

namespace App\Jobs;

use App\Services\MensajeService;
use App\Services\MLUsuarioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class CheckMeliPackStatus implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function backoff()
{
    return [10, 30, 60, 120, 300];
}

	/**
	 * Create a new job instance.
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Execute the job.
	 */
	public function handle()
	{
		$user = app(MLUsuarioService::class)->datosUsuario();
		if (!$user) return;
		$messages = app(MensajeService::class)->getSinLeer();
		foreach ($messages as $message) {
			Log::info("CheckMeliPackStatus ejecutado", ['data' => $message['resource']]);
			app(MensajeService::class)->getPack($message['resource']);
		}
	}
}
