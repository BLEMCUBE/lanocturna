<?php

namespace App\Services;

use App\Models\MercadoLibrePregunta;

class PreguntaService
{
	public function updateOrCreate($question)
	{
		//dd($item['item_id']);
		$data =	MercadoLibrePregunta::updateOrCreate(
			['mercadolibre_pregunta_id' => $question['id']],
			[
				'item_id' => $question['item_id'],
				'seller_id' => $question['seller_id'],
				'text' => $question['text'],
				'status' => $question['status'],
				'date_created' => $question['date_created'],
				//'answer' => $question['answer']??null,
				'from_user_id' => $question['from']['id'] ?? null,
				'payload' => $question,
			]
		);
		return $data;
	}
}
