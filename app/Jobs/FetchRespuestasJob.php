<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Models\MLPregunta;
use App\Jobs\DetalleItemJob;
use App\Models\MLItem;
use App\Models\MLRespuesta;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchRespuestasJob implements ShouldQueue
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

		$fechaInicio = Carbon::now()
			->subDays(1)
			//->subMinute(30)
			->format('Y-m-d H:i:s');
		$fechaFin = Carbon::now()
			->setHour(23)
			->setMinute(59)
			->setSecond(0)
			->format('Y-m-d H:i:s');

		$preguntas = MLPregunta::select('pregunta_id')
			->whereDate('date_created', '>=', $fechaInicio)
			->whereDate('date_created', '<=', $fechaFin)
			->groupBy('pregunta_id')
			->get();


		if (!is_null($preguntas)) {
			foreach ($preguntas as $key => $pregunta) {
				$response = $ml->apiGetDos("/questions/{$pregunta['pregunta_id']}", $this->meliUserId);

				if ($response['success']) {

					$pre = $response['body'];
					$respuesta = $response['body']['answer'];
					if (!is_null($respuesta)) {
						MLRespuesta::updateOrCreate(
							['pregunta_id' => $pre['id']],
							[
								'pregunta_id' => $pre['id'],
								'from_user_id' => $pre['from']['id'] ?? null,
								'date_created' => $respuesta['date_created'],
								'text' => $respuesta['text'],
								'payload' => $respuesta
							]
						);
						$row = MLItem::where('item_id', '=', $pre['item_id'])->first();
						if (is_null($row)) {
							dispatch((new DetalleItemJob($pre['item_id'], $this->clientId))->onQueue('meli'));
						}
					}
				}
			}
		}
	}
}
