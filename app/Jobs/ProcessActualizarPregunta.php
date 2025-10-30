<?php

namespace App\Jobs;

use App\Http\Controllers\MercadoLibre\ItemsController;
use App\Http\Controllers\MercadoLibre\PreguntasController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessActualizarPregunta implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $payload;
	/**
	 * Create a new job instance.
	 */
	public function __construct($payload)
	{
		$this->payload = $payload;
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{

		app(PreguntasController::class)->cambiarEstado($this->payload);
	}
	//

}
