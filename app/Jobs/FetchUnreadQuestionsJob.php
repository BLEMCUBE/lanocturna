<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\MLUsuarioService;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibreService;
use App\Models\MercadoLibrePregunta;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchUnreadQuestionsJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function handle()
	{
		$user = app(MLUsuarioService::class)->datosUsuario();
		if (!$user) return;
		MercadoLibrePregunta::where('status', '=', 'UNANSWERED')
			->update(['status' => 'ANSWERED']);
		$ml = app(MercadoLibreService::class);
		$fechaInicio = Carbon::now()
			->subDays(15)
			->startOfDay()
			->format('Y-m-d\TH:i:s.vP');
		$fechaFin = Carbon::now()
			->setHour(23)
			->setMinute(59)
			->setSecond(0)
			->format('Y-m-d\TH:i:s.vP');

		$parametros = [
			'seller_id' => $user->meli_user_id,
			'status' => 'UNANSWERED',
			'api_version' => '4',
			'date_created.from' => $fechaInicio,
			'date_created.to' => $fechaFin,
		];
		$response = $ml->apiGetDos('/questions/search', $user->meli_user_id, $parametros);
		$questions = $response['body'] ?? [];
		Log::info("FetchUnreadQuestionsJob questions", ['data' => $questions]);
		foreach ($questions['questions'] as $key => $q) {
			MercadoLibrePregunta::updateOrCreate(
				['mercadolibre_pregunta_id' => $q['id']],
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
		}
	}
}
