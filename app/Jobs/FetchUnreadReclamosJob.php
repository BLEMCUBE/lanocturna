<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Models\MLReclamo;
use App\Services\MercadoLibre\ReclamoService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchUnreadReclamosJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

	public function handle()
	{
		if (!$this->clientId) return;

		$ml = app(MercadoLibreService::class)->forClient($this->clientId);

		$oldRelamos = MLReclamo::where('status', '!=', 'closed')
		->where('meli_user_id', '=', $this->clientId)
		->get();

		if ($oldRelamos === null) return;

		foreach ($oldRelamos as $key => $value) {
			$oldUpdated = $value['last_updated'];
			if ($oldUpdated === null) continue;
			$response = $ml->apiGetDos('/post-purchase/v1/claims/' . $value['reclamo_id'], $this->meliUserId);

			$item = $response['body'] ?? null;
			if ($item === null) continue;
			//if ($item['last_updated']  !== $oldUpdated) {

				Log::info("claims", ['data' => $item]);
				// Guardar reclamo
				MLReclamo::updateOrCreate(
					['reclamo_id' => $item['id']],
					[
						'meli_user_id'   => $this->clientId,
						'reclamo_id' => $item['id'],
						'resource'     => $item['resource'] ?? null,
						'type'     => $item['type'] ?? null,
						'stage'     => $item['stage'] ?? null,
						'resource_id'    => $item['resource_id'] ?? null,
						'reason'   => $item['resolution']['reason'] ?? null,
						'status'      => $item['status'] ?? 'opened',
						'reason_id'   => $item['reason_id'] ?? null,
						'date_created'     =>  $item['date_created'] ?? null,
						'last_updated'     =>  $item['last_updated'] ?? null,
						'payload'     => $item,
					]
				);
			//}
			app(ReclamoService::class)->mensajes($item['id'], $this->clientId);
		}
	}
}
