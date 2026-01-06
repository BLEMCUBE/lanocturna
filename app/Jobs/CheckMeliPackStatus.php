<?php

namespace App\Jobs;

use App\Models\MLMensaje;
use App\Models\MLOrden;
use App\Services\MercadoLibre\MensajeService;
use App\Services\MercadoLibre\MercadoLibreService;
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
		$ml = app(MercadoLibreService::class)->forClient($this->clientId);
		foreach ($messages as $message) {

			$this->getPack($message['resource'], $this->clientId, $this->meliUserId, $ml);
			//app(MensajeService::class)->forClient($this->clientId)->getPack($message['resource'],$this->clientId);
		}
	}

	/**
	 * Obtener un pack completo
	 */
	public function getPack($packId, $clientId, $userId, $ml)
	{
		$offset = 0;
		$limit = 100;

		$parametros = [
			'tag' => 'post_sale',
			'mark_as_read' => false,
			'offset' => $offset,
			'limit' => $limit,
		];


		if (!$userId) return [];
		do {
			$response = $ml->apiGet("/messages" . $packId, $userId, $parametros);
			$messages = $response['messages'] ?? [];

			foreach ($messages as $msg) {
				$created = $msg['message_date']['created'] ?? null;
				$read = $msg['message_date']['read'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($userId);
				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;



				MLMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
						'date_created' => $created,
						'text' => strval($msg['text']),
						'attachment_path' => $msg['message_attachments'][0]['filename']
							?? null,
						// si read ≠ null → lo leyó alguien → marcar como leído
						'is_read' => $read ? 1 : 0,
						// marcar si lo envió el vendedor
						'is_from_seller' => $fromSeller ? 1 : 0,
						'client_id' => $clientId,
						// guardar JSON entero
						'payload' => $msg,

					]
				);
			}

			// Paginación
			$offset += $limit;
			$total = $response['paging']['total'] ?? $offset;
		} while ($offset < $total);
	}
}
