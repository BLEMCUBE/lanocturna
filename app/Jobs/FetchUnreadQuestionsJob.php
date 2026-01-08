<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Models\MLPregunta;
use App\Models\MLItem;
use App\Models\MLRespuesta;
use App\Services\MercadoLibre\ItemService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchUnreadQuestionsJob implements ShouldQueue
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
		MLPregunta::where('status', '=', 'UNANSWERED')
			->where('seller_id', $this->meliUserId)
			->update(['status' => 'ANSWERED']);
		$ml = app(MercadoLibreService::class)->forClient($this->clientId);

		$fechaInicio = Carbon::now()
			->subDays(2)
			->startOfDay()
			->format('Y-m-d\TH:i:s.vP');

		$fInicio = Carbon::now()
			->subDays(2)
			->startOfDay()
			->format('Y-m-d');
		$fechaFin = Carbon::now()
			->setHour(23)
			->setMinute(59)
			->setSecond(0)
			->format('Y-m-d\TH:i:s.vP');
		$fFin = Carbon::now()
			->setHour(23)
			->setMinute(59)
			->setSecond(0)
			->format('Y-m-d');

		$parametros = [
			'seller_id' => $this->meliUserId,
			'status' => 'UNANSWERED',
			'api_version' => '4',
			'date_created.from' => $fechaInicio,
			'date_created.to' => $fechaFin,
		];
		$par2 = [
			'seller_id' => $this->meliUserId,
			'sort_fields' =>'date_created',
			'sort_types' =>'DESC',

			'status' => 'UNANSWERED',
			'api_version' => '4',
		];

		//$response = $ml->apiGetDos('/questions/search', $this->meliUserId, $parametros);
		$response = $ml->apiGetDos('/my/received_questions/search', $this->meliUserId, $par2);

		$questions = $response['body'];
		$allQuestions = collect($questions['questions']);

		$filtered = $allQuestions->filter(
			fn($q) =>
			Carbon::parse($q['date_created'])->between(
				$fInicio.' 00:00:00',
				$fFin.' 23:59:59'
			)
		);
		Log::info("FetchUnreadQuestionsJob questions", ['data' => $filtered]);

		foreach ($filtered as $key => $q) {

			MLPregunta::updateOrCreate(
				['pregunta_id' => $q['id']],
				[
					'item_id' => $q['item_id'],
					'seller_id' => $q['seller_id'],
					'text' => $q['text'],
					'status' => $q['status'],
					'date_created' => $q['date_created'],
					'from_user_id' => $q['from']['id'] ?? null,
					'payload' => $q,
				]
			);

			if (($q['answer'] != null) && isset($q['answer'])) {
				$exist = MLRespuesta::where('pregunta_id', '=',  $q['id'])->first();
				if (!$exist) continue;
				MLRespuesta::updateOrCreate(
					['pregunta_id' => $q['id']],
					[
						'pregunta_id' => $q['id'],
						'from_user_id' => $q['from']['id'] ?? null,
						'date_created' => $q['answer']['date_created'],
						'text' => $q['answer']['text'],
						'payload' => $q['answer']
					]
				);
			}

			$row = MLItem::where('item_id', '=',  $q['item_id'])->first();
			$ml = app(MercadoLibreService::class)->forClient($this->clientId);
			$item = $ml->apiGet('/items/' . $q['item_id'], $this->meliUserId, []);
			if (is_null($row)) {
				app(ItemService::class)->crear($item);
			} else {
				//$item = $ml->apiGet('/items/' . $q['item_id'], $this->meliUserId, []);
				app(ItemService::class)->actualizar($q['item_id'], $item);
			}
		}
	}
}
