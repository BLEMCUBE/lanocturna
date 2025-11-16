<?php

namespace App\Jobs;

use App\Services\MensajeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessActualizarMensaje implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public string $packId;
	/**
	 * Create a new job instance.
	 */
	public function __construct($packId)
	{

		$this->packId = $packId;
	}

	/**
	 * Execute the job.
	 */
	public function handle()
	{
	}
	//


}
