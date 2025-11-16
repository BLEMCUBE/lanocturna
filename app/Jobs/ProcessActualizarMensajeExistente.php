<?php

namespace App\Jobs;

use App\Services\MensajeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessActualizarMensajeExistente implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct()
	{
		// No se necesitan parámetros aquí
	}

	/**
	 * Execute the job.
	 */
	public function handle()
	{
		app(MensajeService::class)->marcarLeido();
	}
	//

}
