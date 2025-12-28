<?php

namespace App\Jobs;

use App\Services\MercadoLibre\MensajeService;
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


	public ?string $clientId;
	public ?string $meliUserId;
	public ?string $resourceId;

	/**
	 * Constructor flexible: permite jobs con datos (webhook)
	 * o sin datos (cron scheduler)
	 */
	public function __construct(
		?string $clientId = null,
		?string $meliUserId = null,
		?string $resourceId = null
	) {
		$this->clientId   = $clientId;
		$this->meliUserId = $meliUserId;
		$this->resourceId = $resourceId;
	}

	/**
	 * Execute the job.
	 */
	public function handle()
	{
		$messages = app(MensajeService::class)->forClient($this->clientId)->getSinLeer($this->clientId);
		foreach ($messages as $message) {
			app(MensajeService::class)->forClient($this->clientId)->getPack($message['resource'],$this->clientId);
		}
	}
}
